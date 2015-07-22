<?php
namespace Sygefor\Bundle\ActivityReportBundle\Report\CrosstabReport;

/**
 * Class PivotFormatter.
 */
class CrosstabFormatter
{
    private $terms;

    /**
     * @var boolean $debug
     */
    private $debug;

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * Get the data mapping
     *
     * @param $rows
     * @param $agg
     *
     * @return array
     */
    protected function getMapping($rows, $agg)
    {
        $mapping = array();
        $aggs = array();

        $map = function($rows, $agg = null, $depth = 0) use (&$map, &$mapping, &$aggs) {
            $aggs[$depth] = $agg;
            foreach($rows as $item) {
                $key = $item["key"];
                $mapping[$depth][$key] = array(
                  "key" => $key,
                  "value" => 0,
                  "label" => $key
                );
                if(isset($this->terms[$agg][$key])) {
                    $mapping[$depth][$key]['label'] = $this->terms[$agg][$key];
                }
                if(isset($item['data'])) {
                    $map($item['data'], $item['agg'], $depth+1);
                }
            }
        };
        $map($rows, $agg);

        // remove keys
        $mapping = array_map(function($map) { return array_values($map); }, $mapping);

        foreach($mapping as $depth => &$map) {
            // reorder mapping by terms
            if(!empty($this->terms[$aggs[$depth]])) {
                $map = $this->reorderMapByTerms($map, $this->terms[$aggs[$depth]]);
            }

            // put 'Autre' always at the end
            $index = $this->getIndexByKey($map, 'Autre');
            if($index !== false) {
                $item = $map[$index];
                unset($map[$index]);
                array_push($map, $item);
            }
        }

        return $mapping;
    }

    /**
     * Reorder mapping by term list
     */
    protected function reorderMapByTerms($mapping, $terms)
    {
        $keys = array();
        foreach($terms as $key => $term) {
            $keys[] = is_string($key) ? $key : $term;
        }
        $ordered = array();
        foreach($keys as $key) {
            $done = false;
            foreach($mapping as $k => $item) {
                if($item['key'] == $key) {
                    if(isset($terms[$key])) {
                        $item['label'] = $terms[$key];
                    }
                    $ordered[] = $item;
                    unset($mapping[$k]);
                    $done = true;
                }
            }
            if(!$done) {
                $ordered[] = array(
                  'key' => $key,
                  'label' => isset($terms[$key]) ? $terms[$key] : $key,
                  'value' => 0
                );
            }
        }
        $ordered += $mapping;
        return $ordered;
    }

    /**
     * Process the rows, populate the final cols
     * @param $rows
     */
    protected function getProcessedRows($data, &$cols, $inverse = false)
    {
        $parent = null;
        $rows = $this->getRows($data, $parent);
        $agg = $parent['agg'];

        $cols = array();
        $mapping = $this->getMapping($rows, $agg);

        if($mapping) {
            // process mapping
            $process = function($rows, $depth = 0) use (&$mapping, &$process) {
                $last = !isset($mapping[$depth + 1]);
                //$rows = $this->reorderValuesByKeys($rows, $mapping[$depth], $last); // why $add = $last ?
                $rows = $this->reorderValuesByKeys($rows, $mapping[$depth], true);
                foreach($rows as &$row) {
                    if(!$last) {
                        if(!isset($row['data'])) {
                            $row['data'] = array();
                        }
                        $row['data'] = $process($row['data'], $depth + 1);
                    }
                }
                return $rows;
            };
            $rows = $process($rows);
            $cols = end($mapping);
            if($inverse) {
                $rows = $this->getInversedRows($rows, $cols);
            }
        }

        // return
        return $rows;
    }

    /**
     * @param $data
     * @param $cols
     * @param $depth
     * @param $parent
     *
     * @return array
     */
    protected function getRows($data, &$parent = null, $depth = -1, $agg_name = null)
    {
        $rows = array();

        foreach($data as $key => $value) {
            if($key == 'buckets') {
                $parent['agg'] = $agg_name;
                $depth++;
                foreach($value as $bucket) {
                    $row = array(
                      'key' => $bucket['key'],
                      'value' => $bucket['doc_count']
                    );
                    $subRows = $this->getRows($bucket, $row, $depth, $agg_name);
                    if($subRows) {
                        // if there is subrows, get it and calculate sum
                        $row['data'] = $subRows;
                        $total = array_sum(array_map(function($row) { return $row['value']; }, $subRows));
                        // if total < parent doc_count, add difference to a 'Autre' facet
                        if($row['value'] > $total) {
                            $index = $this->getIndexByKey($row['data'], 'Autre');
                            if($index !== false) {
                                $row['data'][$index]['value'] += $row['value'] - $total;
                            } else {
                                $row['data'][] = array(
                                  'key' => 'Autre',
                                  'value' => $row['value'] - $total
                                );
                            }
                        }
                    }
                    $rows[] = $row;
                }
            } elseif(is_array($value)) {
                $_rows = $this->getRows($value, $parent, $depth, $key);
                if(count($_rows)) {
                    // if any rows returned, replace the current one
                    $rows = $_rows;
                }
            } elseif($parent && in_array($key, array('value', 'doc_count'))) {
                $parent['value'] = $value;
            }
        }

        return $rows;
    }

    /**
     * Sort col data by the global cols
     * + fill the value total of the cols
     *
     * @param $array
     * @param $mapping
     * @return array
     */
    protected function reorderValuesByKeys($array, &$mapping, $add = false) {
        $ordered = array();
        foreach($mapping as &$col) {
            $done = false;
            foreach($array as $key => $value) {
                if($value['key'] == $col['key']) {
                    $value['label'] = isset($col['label']) ? $col['label'] : $col['key'] ;
                    $ordered[] = $value;
                    $col['value'] += $value['value'];
                    unset($array[$key]);
                    $done = true;
                    break;
                }
            }
            // if the col doesn't exist, add it to 0
            if(!$done && $add) {
                $ordered[] = array(
                  'key' => $col['key'],
                  'label' => isset($col['label']) ? $col['label'] : $col['key'],
                  'value' => 0
                );
            }
        }
        return $ordered;
    }

    /**
     * @param $array
     * @param $value
     * @param string $key
     * @return bool|int|string
     */
    protected function getIndexByKey($array, $value, $key = 'key') {
        foreach($array as $index => $item) {
            if($item[$key] == $value) {
                return $index;
            }
        }
        return false;
    }

    /**
     * @param mixed $terms
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
    }

    /**
     * @param array $rows
     * @param array $cols
     * @return array
     */
    public function getInversedRows($rows, &$cols)
    {
        $cols = array();
        $new = array();
        foreach($rows as $row) {
            $subrows = $row['data'];
            unset($row['data']);
            if(!isset($cols[$row['key']])) {
                $cols[$row['key']] = $row;
            }
            foreach($subrows as $subrow) {
                $n = &$new[$subrow['key']];
                if(!isset($n)) {
                    $n = $subrow;
                }
                $row['value'] = $subrow['value'];
                $n['data'][] = $row;
            }
        }

        // recalculate
        foreach($new as &$item) {
            $item['value'] = array_sum(array_map(function($i) { return $i['value']; }, $item['data']));
        }

        $cols =  array_values($cols);
        return array_values($new);
    }

    /**
     * @param $buckets
     *
     * @return array
     */
    public function format($data, $inverse = false)
    {
        $rows = $this->getProcessedRows($data, $cols, $inverse);

        $value = 0;
        foreach ($cols as $col) {
            $value += $col['value'];
        }

        return array(
          'cols' => array_values($cols),
          'rows' => $rows,
          'value' => $value
        );
    }

}
