<?php
/**
* @package Test-Plugin
*/
namespace Inc\Pages;

use \Inc\Base\BaseController;
use \Inc\Api\SettingsApi;
use \Inc\Api\Callbacks\AdminCallbacks;
use \Inc\Api\Callbacks\ManagerCallbacks;

/**
*
*/
class Dashboard extends BaseController
{
    public $settings;
    public $callbacks;
    public $callbacks_mgr;

    public $pages = array();

    public function register()
    {
        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
        $this->callbacks_mgr = new ManagerCallbacks();

        $this->setPages();

        $this->setSettings();
        $this->setSections();
        $this->setFields();

        $this->settings->addPages( $this->pages )->withSubPage( 'Dashboard' )->register();
    }

    public function setPages()
    {
        $this->pages = array(
            array(
                'page_title' => 'Mauve Plugin',
                'menu_title' => 'Mauve',
                'capability' => 'manage_options',
                'menu_slug' => 'mauve_plugin',
                'callback' => array( $this->callbacks, 'adminDashboard' ),
                'icon_url' => 'dashicons-buddicons-groups',
                'position' => 110
            )
        );
    }

    public function setSettings()
    {
        $args = array(
            array(
                    'option_group' => 'mauve_plugin_settings',
                    'option_name' => 'mauve_plugin',
                    'callback' => array( $this->callbacks_mgr, 'checkboxSanitize' )
                )
            );

        $this->settings->setSettings( $args );
    }

    public function setSections()
    {
        $args = array(
            array(
                'id' => 'plugin_admin_index',
                'title' => 'Settings Manager',
                'callback' => array( $this->callbacks_mgr, 'adminSectionManager' ),
                'page' => 'mauve_plugin'
            )
        );

        $this->settings->setSections( $args );
    }

    public function setFields()
    {
        $args = array();

        foreach( $this->managers as $key => $value) {
            $args[] = array(
                'id' => $key,
                'title' => $value,
                'callback' => array( $this->callbacks_mgr, 'checkboxField' ),
                'page' => 'mauve_plugin',
                'section' => 'plugin_admin_index',
                'args' => array(
                    'option_name' => 'mauve_plugin',
                    'label_for' => $key,
                    'class' => 'ui-toggle'
                )
            );
        }

        $this->settings->setFields( $args );
    }
}
