<?php
/**
 * Created by PhpStorm.
 * User: erwan
 * Date: 3/22/19
 * Time: 1:01 PM
 */

namespace FrontBundle\Utils;

use Symfony\Component\Form\FormInterface;

/**
 * Class FormPagination.
 */
class FormPagination
{
	/**
	 * @param FormInterface $form
	 *
	 * @return string
	 */
	public static function getFormValues(FormInterface $form)
	{
		$data = [];
		foreach ($form->all() as $key => $child) {
			$data[$key] = $child->getViewData();
		}

		return json_encode($data);
	}

	/**
	 * Get form values to set to pagination navigation links
	 *
	 * @param FormInterface $form
	 *
	 * @return array
	 */
	public static function getPaginationFieldValues(FormInterface $form)
	{
		$paginationFormOptions = [];
		foreach ($form->all() as $child) {
			if ($child->getData()) {
				$value = $child->getViewData();
				if ($child->getData() instanceof \DateTime) {
					$value = $child->getData()->format('Y-m-d');
				}
				else if (is_array($value)) {
					$value = json_encode($value);
				}
				$paginationFormOptions[$child->getName()] = $value;
			}
		}

		return $paginationFormOptions;
	}
}