<?php
/**
 * @package Test-Plugin
 */
namespace Inc\Base;

use \Inc\Base\BaseController;
use \Inc\Api\SettingsApi;
use \Inc\Api\Callbacks\AdminCallbacks;

/**
*
*/
class MembershipController extends BaseController
{
    public $subpages = array();
    public $callbacks;

    public function register()
    {
        if ( ! $this->activated( 'membership_manager' ) ) return;

        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();

        $this->setSubPages();
        $this->settings->addSubPages( $this->subpages )->register();

        add_action( 'init', array( $this, 'activate') );
    }

    public function setSubPages()
    {
        $this->subpages = array(
            array(
                'parent_slug' => 'mauve_plugin',
                'page_title' => 'Membership Manager',
                'menu_title' => 'Membership Manager',
                'capability' => 'manage_options',
                'menu_slug' => 'mauve_membership',
                'callback' => array( $this->callbacks, 'adminMembership' )
            )
        );
    }

    public function activate ()
    {
        register_post_type( ' mauve_products' ,
        array(
            'labels' => array(
                'name' => 'Products',
                'singular_name' => 'Product'
            ),
            'public' => true,
            'has_archive' => true
        )
    );
}

}
