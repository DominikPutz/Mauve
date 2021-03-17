<?php
/**
 * @package Test-Plugin
 */
namespace Inc\Base;

use \Inc\Base\BaseController;
use \Inc\Api\SettingsApi;
use \Inc\Api\Callbacks\AdminCallbacks;
use \Inc\Api\Callbacks\TaxonomyCallbacks;

/**
*
*/
class CustomTaxonomyController extends BaseController
{
    public $settings;
    public $callbacks;
    public $taxonomy_callbacks;
    public $subpages = array();
    public $taxonomies = array();

    public function register()
    {
        if ( ! $this->activated( 'taxonomy_manager' ) ) return;

        $this->settings = new SettingsApi();
        $this->callbacks = new AdminCallbacks();
        $this->taxonomy_callbacks = new TaxonomyCallbacks();

        $this->setSubPages();

        $this->setSettings();
        $this->setSections();
        $this->setFields();

        $this->settings->addSubPages( $this->subpages )->register();

        $this->storeCustomTaxonomies();

        if ( ! empty( $this->taxonomies ) ) {
        	add_action( 'init', array( $this, 'registerCustomTaxonomy' ));
        }
    }

    public function setSubPages()
    {
        $this->subpages = array(
            array(
                'parent_slug' => 'mauve_plugin',
                'page_title' => 'Custom Taxonomies',
                'menu_title' => 'Taxonomy Manager',
                'capability' => 'manage_options',
                'menu_slug' => 'mauve_taxonomy',
                'callback' => array( $this->callbacks, 'adminTaxonomy' )
            )
        );
    }

    public function setSettings()
    {
        $args = array(
            array(
                'option_group' => 'mauve_plugin_taxonomy_settings',
                'option_name' => 'mauve_plugin_taxonomy',
                'callback' => array( $this->taxonomy_callbacks, 'taxonomySanitize' )
            )
        );

        $this->settings->setSettings( $args );
    }

    public function setSections()
    {
        $args = array(
            array(
                'id' => 'mauve_taxonomy_index',
                'title' => 'Custom Taxonomy Manager',
                'callback' => array( $this->taxonomy_callbacks, 'taxonomySectionManager' ),
                'page' => 'mauve_taxonomy'
            )
        );

        $this->settings->setSections( $args );
    }

    public function setFields()
    {
        $args = array(
            array(
                'id' => 'taxonomy',
                'title' => 'Custom Taxonomy ID',
                'callback' => array( $this->taxonomy_callbacks, 'textField' ),
                'page' => 'mauve_taxonomy',
                'section' => 'mauve_taxonomy_index',
                'args' => array(
                    'option_name' => 'mauve_plugin_taxonomy',
                    'label_for' => 'taxonomy',
                    'placeholder' => 'eg. Genre',
                    'array' => 'taxonomy'
                )
            ),
            array(
                'id' => 'singular_name',
                'title' => 'Singular Name',
                'callback' => array( $this->taxonomy_callbacks, 'textField' ),
                'page' => 'mauve_taxonomy',
                'section' => 'mauve_taxonomy_index',
                'args' => array(
                    'option_name' => 'mauve_plugin_taxonomy',
                    'label_for' => 'singular_name',
                    'placeholder' => 'eg. Genre',
                    'array' => 'taxonomy'
                )
            ),
            array(
                'id' => 'hierarchical',
                'title' => 'Hierarchical',
                'callback' => array( $this->taxonomy_callbacks, 'checkboxField' ),
                'page' => 'mauve_taxonomy',
                'section' => 'mauve_taxonomy_index',
                'args' => array(
                    'option_name' => 'mauve_plugin_taxonomy',
                    'label_for' => 'public',
                    'class' => 'ui-toggle',
                    'array' => 'taxonomy'
                )
            ),
            array(
                'id' => 'objects',
                'title' => 'Post Types',
                'callback' => array( $this->taxonomy_callbacks, 'checkboxPostTypesField' ),
                'page' => 'mauve_taxonomy',
                'section' => 'mauve_taxonomy_index',
                'args' => array(
                    'option_name' => 'mauve_plugin_taxonomy',
                    'label_for' => 'public',
                    'class' => 'ui-toggle',
                    'array' => 'taxonomy'
                )
            )
        );

        $this->settings->setFields( $args );
    }

    public function storeCustomTaxonomies()
	{
		// get the taxonomies array
		$options = get_option( 'mauve_plugin_taxonomy' ) ?: array();

		// store those info into an array
		foreach ($options as $option) {
			$labels = array(
				'name'              => $option['singular_name'],
				'singular_name'     => $option['singular_name'],
				'search_items'      => 'Search ' . $option['singular_name'],
				'all_items'         => 'All ' . $option['singular_name'],
				'parent_item'       => 'Parent ' . $option['singular_name'],
				'parent_item_colon' => 'Parent ' . $option['singular_name'] . ':',
				'edit_item'         => 'Edit ' . $option['singular_name'],
				'update_item'       => 'Update ' . $option['singular_name'],
				'add_new_item'      => 'Add New ' . $option['singular_name'],
				'new_item_name'     => 'New ' . $option['singular_name'] . ' Name',
				'menu_name'         => $option['singular_name'],
			);

			$this->taxonomies[] = array(
				'hierarchical'      => isset($option['hierarchical']) ? true : false,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
                'show_in_rest'      => true,
				'rewrite'           => array( 'slug' => $option['taxonomy'] ),
                'objects'           => isset( $option[ 'objects' ] ) ? $option[ 'objects' ] : null
			);

		}
	}

    public function registerCustomTaxonomy()
    {
        foreach ($this->taxonomies as $taxonomy) {
            $objects = isset( $taxonomy[ 'objects' ] ) ? array_keys($taxonomy['objects']) : null;
            register_taxonomy( $taxonomy['rewrite']['slug'], $objects, $taxonomy );
        }
    }
}
