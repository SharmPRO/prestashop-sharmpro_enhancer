<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Spro_enhancer extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'spro_enhancer';
        $this->tab = 'administration';
        $this->version = '0.0.1';
        $this->author = 'SharmPRO';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('SharmPRO PrestaShop Enhancer');
        $this->description = $this->l('Enhance PrestaShop Features');

        $this->confirmUninstall = $this->l('Are You sure you want to uninstall?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('SPRO_ENHANCER_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('SPRO_ENHANCER_LIVE_MODE');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitSpro_enhancerModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSpro_enhancerModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'SPRO_ENHANCER_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('BO Order List Carrier'),
                        'name' => 'SPRO_ENHANCER_BO_ORDER_LIST_CARRIER',
                        'is_bool' => true,
                        'desc' => $this->l('Show Carrier in BO Order List'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('BO Order List isGuest'),
                        'name' => 'SPRO_ENHANCER_BO_ORDER_LIST_GUEST',
                        'is_bool' => true,
                        'desc' => $this->l('Show isGuest in BO Order List'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('BO Order List Note'),
                        'name' => 'SPRO_ENHANCER_BO_ORDER_LIST_NOTE',
                        'desc' => $this->l('Show Note in BO Order List'),
                        'options' => array(
                                    'query' => array(
                                        array(
                                            'id_option' => 0,       // The value of the 'value' attribute of the <option> tag.
                                            'name' => 'None'    // The value of the text content of the  <option> tag.
                                          ),
                                                array(
                                                  'id_option' => 1,       // The value of the 'value' attribute of the <option> tag.
                                                  'name' => 'Internal Note'    // The value of the text content of the  <option> tag.
                                                ),
                                                array(
                                                  'id_option' => 2,
                                                  'name' => 'Shipping Note'
                                                ),
                                    ),
                                    'id' => 'id_option',
                                    'name' => 'name' 
                                   )              
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('BO Order List Message'),
                        'name' => 'SPRO_ENHANCER_BO_ORDER_LIST_MESSAGE',
                        'is_bool' => true,
                        'desc' => $this->l('Show Message in BO Order List'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    
                    array(
                        'type' => 'switch',
                        'label' => $this->l('BO Order List hide Reference'),
                        'name' => 'SPRO_ENHANCER_BO_ORDER_LIST_REMOVE_REFERENCE',
                        'is_bool' => true,
                        'desc' => $this->l('Hide Reference in BO Order List'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'SPRO_ENHANCER_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'SPRO_ENHANCER_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'SPRO_ENHANCER_LIVE_MODE' => Configuration::get('SPRO_ENHANCER_LIVE_MODE', true),
            'SPRO_ENHANCER_BO_ORDER_LIST_CARRIER' => Configuration::get('SPRO_ENHANCER_BO_ORDER_LIST_CARRIER', false),
            'SPRO_ENHANCER_BO_ORDER_LIST_GUEST' => Configuration::get('SPRO_ENHANCER_BO_ORDER_LIST_GUEST', false),
            'SPRO_ENHANCER_BO_ORDER_LIST_NOTE' => Configuration::get('SPRO_ENHANCER_BO_ORDER_LIST_NOTE', false),
            'SPRO_ENHANCER_BO_ORDER_LIST_MESSAGE' => Configuration::get('SPRO_ENHANCER_BO_ORDER_LIST_MESSAGE', false),
            'SPRO_ENHANCER_BO_ORDER_LIST_REMOVE_REFERENCE' => Configuration::get('SPRO_ENHANCER_BO_ORDER_LIST_REMOVE_REFERENCE', false),
            
            'SPRO_ENHANCER_ACCOUNT_EMAIL' => Configuration::get('SPRO_ENHANCER_ACCOUNT_EMAIL', 'stefano@sharmpro.com'),
            'SPRO_ENHANCER_ACCOUNT_PASSWORD' => Configuration::get('SPRO_ENHANCER_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
}
