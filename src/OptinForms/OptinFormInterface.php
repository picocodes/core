<?php

namespace MailOptin\Core\OptinForms;


interface OptinFormInterface {

	/**
	 * HTML body structure of the optin form
	 *
	 * @return string
	 */
	public function optin_form();

	/**
	 * CSS stylesheet for the optin form
	 *
	 * @return string
	 */
	public function optin_form_css();

	/**
	 * Customizer JavaScript for the template
	 *
	 * @return string
	 */
	public function optin_script();

	/**
	 * Full HTML doctype markup preview of a optin form.
	 *
	 * @return string
	 */
	public function get_preview_structure();
	
	/**
	 * HTML and CSS structure of an optin form.
	 * 
	 * @return string
	 */
	public function get_optin_form_structure();

	/**
	 * Return array of fonts used by optin form.
	 *
	 * @return string
	 */
	public function get_optin_form_fonts();
}