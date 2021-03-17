<?php

namespace Hurrytimer;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Hurrytimer\Utils\Helpers;

class Campaign
{

    /**
     * Campaign custom post ID
     *
     * @var int
     */
    private $id;

    /**
     * Campaign mode.
     *
     * @see C.php
     *
     * @var int
     */
    public $mode = C::MODE_REGULAR;

    /**
     * Evergreen duration array.
     *
     * @see getDuration()
     *
     * @var array
     */
    public $duration;

    /**
     * Recurrence duration
     *
     * @var int
     */
    public $recurringDuration;

    /**
     * Recurrence start date/time
     *
     * @var string
     */
    public $recurringStartTime;

    /**
     * Recurrence end option
     *
     * @var int
     */
    public $recurringEnd = C::RECURRING_END_NEVER;

    /**
     * Recurrence frequency
     *
     * @see C
     * @var string
     */
    public $recurringFrequency = C::RECURRING_DAILY;

    /**
     * Recurrence interval
     *
     * @var int
     */
    public $recurringInterval = 1;

    /**
     * Recurrence count
     *
     * @var int
     */
    public $recurringCount = 2;

    /**
     * Recurrence end date/time
     *
     * @var
     */
    public $recurringUntil;

    /**
     * Recurrence allowed days
     *
     * @var array
     */
    public $recurringDays = [ 0, 1, 2, 3, 4, 5, 6 ];

    /**
     * Restart option after expiration.
     *
     * @see \Hurrytimer\CampaignRestart
     *
     * @var int
     */
    public $restart;

    /**
     * Headline text.
     *
     * @var string
     */
    public $headline = "Hurry Up!";

    /**
     * Headline color.
     *
     * @var string
     */
    public $headlineColor = "#000";

    /**
     * Headline size.
     *
     * @var int
     */
    public $headlineSize = 30;

    /**
     * Headline position.
     *
     * @var int
     */
    public $headlinePosition = C::HEADLINE_POSITION_ABOVE_TIMER;

    /**
     * Headline visibility.
     *
     * @var boolean
     */
    public $headlineVisibility = C::YES;

    public $headlineSpacing = 5;

    /**
     * Control labels visibility.
     *
     * @var string
     */
    public $labelVisibility = C::YES;

    /**
     * Show/hide days block.
     *
     * @var string
     */
    public $daysVisibility = C::YES;

    /**
     * Show/hide hours block.
     *
     * @var string
     */
    public $hoursVisibility = C::YES;

    /**
     * Show/hide minutes block.
     *
     * @var string
     */
    public $minutesVisibility = C::YES;

    /**
     * Show/hide seconds block.
     *
     * @var string
     */
    public $secondsVisibility = C::YES;

    /**
     * Regular campaign end date/time.
     *
     * @var string
     */
    public $endDatetime;

    /**
     * Labels texts.
     *
     * @var array
     */
    public $labels
        = [
            'days' => 'days',
            'hours' => 'hrs',
            'minutes' => 'mins',
            'seconds' => 'secs',
        ];

    /**
     * Digit color.
     *
     * @var string
     */
    public $digitColor = "#000";

    /**
     * Digit size.
     *
     * @var int
     */
    public $digitSize = 35;

    /**
     * Redirect action url.
     *
     * @var string
     */
    public $redirectUrl;

    /**
     * Label size.
     *
     * @var int
     */
    public $labelSize = 12;

    /**
     * Label color.
     *
     * @var string
     */
    public $labelColor = "#000";

    /**
     * End action.
     *
     * @var array
     */
    public $actions = [];

    /**
     * WooCommerce compaign position in product page.
     *
     * @var int
     */
    public $wcPosition = C::WC_POSITION_ABOVE_TITLE;

    /**
     * Enable/disable woocommerce integration.
     *
     * @var string
     */
    public $wcEnable = C::NO;

    /**
     * WooCommerce products selection.
     *
     * @var array
     */
    public $wcProductsSelection;

    /**
     * WooCommerce products selection type.
     *
     * @var int
     */
    public $wcProductsSelectionType;

    public $wcConditions;

    /**
     * Timer block border color.
     *
     * @var string
     */
    public $blockBorderColor = "";

    /**
     * Timer Block border width.
     *
     * @var int
     */

    public $blockBorderWidth = 0;

    /**
     * Timer block radius.
     *
     * @var int
     */
    public $blockBorderRadius = 0;

    /**
     * Block size.
     *
     * @var int
     */

    public $blockSize = 50;

    /**
     * Block background color.
     *
     * @var string
     */
    public $blockBgColor = '';

    /**
     * Block spacing.
     *
     * @var int
     */
    public $blockSpacing = 5;

    /**
     * Block padding.
     *
     * @var int
     */
    public $blockPadding = 0;

    /**
     * Block spearator visibility.
     *
     * @var boolean
     */
    public $blockSeparatorVisibility = C::YES;

    /**
     * Label case
     *
     * @var string
     */
    public $labelCase = C::TRANSFORM_UPPERCASE;

    /**
     * Custom CSS
     *
     * @var string
     */
    public $customCss = '';

    /**
     * Block elements display
     * Values: block, inline
     *
     * @var string
     */
    public $blockDisplay = 'block';

    /**
     * Enable sticky bar.
     */
    public $enableSticky = C::NO;

    /**
     * Sticky bar background color.
     *
     * @var string
     */
    public $stickyBarBgColor = '#eee';

    /**
     * Sticky bar close button color.
     *
     * @var string
     */
    public $stickyBarCloseBtnColor = '#fff';

    /**
     * Sticky bar position.
     * Values: top, bottom
     *
     * @var string
     */
    public $stickyBarPosition = 'top';

    /**
     * Sticky bar padding.
     *
     * @var integer
     */
    public $stickyBarPadding = 5;

    /**
     * Sticky bar display pages.
     *
     * @var array
     */
    public $stickyBarPages = [];

    /**
     * Where to display the sticky bar option
     *
     * @var string
     */
    public $stickyBarDisplayOn = 'all_pages';

    /**
     * Show sticky bar close button?
     *
     * @var string
     */
    public $stickyBarDismissible = C::YES;

    /**
     * CTA settings.
     *
     * @var array
     */
    public $callToAction
        = [
            'enabled' => C::NO,
            'new_tab' => C::NO,
            'url' => '',
            'text' => 'Learn More',
            'text_size' => 15,
            'text_color' => '#fff',
            'bg_color' => '#000',
            'y_padding' => 10,
            'x_padding' => 15,
            'border_radius' => 3,
            'spacing' => 5,
        ];

    /**
     * Campaign elements display
     * values: block,inline
     *
     * @var string
     */
    public $campaignDisplay = 'block';

    /**
     * Campaign aligments
     * values: center,left,right
     *
     * @var string
     */
    public $campaignAlign = 'center';

    /**
     * Campaign spacing.
     *
     * @var integer
     */
    public $campaignSpacing = 10;

    /**
     * Campaign horizontal padding.
     *
     * @var integer
     */
    public $campaignXPadding = 10;

    /**
     * Campaign vertical padding.
     *
     * @var integer
     */
    public $campaignYPadding = 10;

    public function __construct( $id )
    {
        $this->recurringStartTime = Carbon::now( hurryt_tz() )->format( 'Y-m-d h:i A' );
        $this->id = $id;
    }

    /**
     * Returns compaign post iD.
     *
     * @return int
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Returns true if the current sticky bar can be displayed on the given page.
     *
     * @param $page_id
     *
     * @return bool
     */
    public function show_sticky_on_page( $page_id )
    {
        if ( $this->enableSticky === C::NO ) {
            return true;
        }

        $can_show = false;
        switch ( $this->getStickyBarDisplayOn() ) {
            case 'all_pages':
                $can_show = true;
                break;
            case 'wc_products_pages':
                if ( function_exists( 'is_product' ) && is_product() ) {
                    $wc_campaign = new WCCampaign();
                    $can_show = ( $wc_campaign->has_campaign( $this, $page_id ) );
                } else {
                    $can_show = false;
                }
                break;
            case 'specific_pages':
                $pages = $this->getStickyBarPages();
                $can_show = in_array( $page_id, $pages );
                break;
        }

        return apply_filters( 'hurryt_show_sticky_bar', $can_show, $this->get_id() );

    }

    /**
     * Returns sticky bar pages.
     *
     * @return array
     */
    public function getStickyBarPages()
    {
        $pagesIds = $this->get_prop( 'sticky_bar_pages' );

        return array_filter( array_map( 'intval', $pagesIds ) );
    }

    /**
     * Set sticky bar pages.
     *
     * @param $value
     */
    public function setStickyBarPages( $value )
    {
        $this->set_prop( 'sticky_bar_pages', $value );
    }

    public function getStickyBarDisplayOn()
    {

        // backward compat.
        $all_pages = $this->get_prop( 'sticky_bar_show_on_all_pages', false );

        if ( $all_pages === C::YES ) {
            $this->setStickyBarDisplayOn( 'all_pages' );
        }

        $this->delete_prop( 'sticky_bar_show_on_all_pages' );

        $value = $this->get_prop( '_hurryt_sticky_bar_display_on', false );

        return !empty( $value ) ? $value : $this->stickyBarDisplayOn;
    }

    public function setStickyBarDisplayOn( $value )
    {
        $this->set_prop( '_hurryt_sticky_bar_display_on', $value );
    }

    /**
     * Get raw setting.
     *
     * @param string $name
     * @param boolean
     *
     * @return mixed
     */
    public function get_prop( $name, $useDefault = true )
    {
        $value = get_post_meta( $this->id, $name, true );
        if ( !empty( $value ) ) {
            return $value;
        }
        if ( $useDefault ) {
            return $this->{Helpers::snakeToCamelCase( $name )};
        }

        return $value;
    }

    public function set_prop( $name, $value )
    {
        $value = !empty( $value ) ? $value : '';
        $getterMethod = Helpers::snakeToCamelCase( $name );
        if ( empty( $value ) && method_exists( $this, $getterMethod ) ) {
            $value = $this->{$getterMethod}();
        }

        update_post_meta( $this->id, $name, $value );
    }

    /**
     * Bulk save for compaign settings.
     *
     * @param $data
     */
    public function storeSettings( $data )
    {
        foreach ( $data as $prop => $value ) {
            $method = Helpers::snakeToCamelCase( "set_{$prop}" );
            if ( method_exists( $this, $method ) ) {
                $this->$method( $value ?: $this->$prop );
            } elseif ( property_exists( __CLASS__, Helpers::snakeToCamelCase( $prop ) ) ) {
                $this->set_prop( $prop, $value );
            }
        }
        if ( !isset( $data[ 'wc_conditions' ] ) ) {
            $this->setWcConditions( [] );
        }
        if ( !isset( $data[ 'sticky_bar_pages' ] ) ) {
            $this->setStickyBarPages( [] );
        }
        self::generate_css();
    }

    //removeIf(pro)

    /**
     * @param int $mode
     * ::PROP
     */
     public function setMode( $mode )
     {
         if ( $mode == C::MODE_RECURRING ) {
             return;
         }
         $this->set_prop( 'mode', $mode );
     }
    //endRemoveIf(pro)

    /**
     * Build User CSS,
     * Then merge it with base one.
     *
     * @return void
     */
    public static function generate_css()
    {

        // Build and return CSS
        ob_start();
        include HURRYT_DIR . 'includes/css.php';
        $css = ob_get_clean();
        $base = file_get_contents( HURRYT_DIR . '/assets/css/base.css' );

        // Merge built and base css.
        $base .= $css;

        // Save to public file.
        file_put_contents( HURRYT_DIR . '/assets/css/hurrytimer.css', $base );
    }

    /**
     * @todo refactor
     */
    public function loadSettings()
    {

        $reflection = new \ReflectionObject( $this );
        foreach ( $reflection->getProperties( \ReflectionProperty::IS_PUBLIC ) as $prop ) {
            $name = $prop->getName();
            $method = 'get' . ucfirst( $name );
            if ( method_exists( $this, $method ) ) {
                $this->{$name} = $this->$method();
            } else {
                $this->{$name} = $this->get_prop( Helpers::camelToSnakeCase( $name ) );
            }
        }
    }

    /**
     * Returns compaign headline.
     * ::PROP
     *
     * @return mixed
     */
    public function getHeadline()
    {
        return $this->id ? get_the_title( $this->id ) : $this->headline;
    }

    public function is_published()
    {
        return get_post_status( $this->id ) === "publish";
    }

    /**
     * Check if WooCommerce is enabled for this campaign.
     *
     * @return bool
     */
    public function is_wc_enabled()
    {
        return $this->getWcEnable() === C::YES;
    }

    /**
     * Check current countdown timer mode
     * ::PROP
     *
     * @return bool
     */
    public function is_evergreen()
    {
        return $this->get_prop( 'mode' ) == C::MODE_EVERGREEN;
    }

    public function is_one_time()
    {
        return $this->get_prop( 'mode' ) == C::MODE_REGULAR;
    }

    public function is_recurring()
    {
        return $this->get_prop( 'mode' ) == C::MODE_RECURRING;

    }

    /**
     * Save compaign endtime for regular mode.
     *
     * @param $date
     */
    public function setEndDatetime( $date )
    {
        $this->set_prop( 'end_datetime', $date ?: $this->defaultEndDatetime() );
    }

    /**
     * Returns default end datetime for regular mode.
     * ::PROP
     *
     * @return false|string
     */
    private function defaultEndDatetime()
    {
        return date( 'Y-m-d h:i A', strtotime( '+1 week' ) );
    }

    /**
     * Save digit color.
     * ::PROP
     *
     * @param $color
     */
    public function setDigitColor( $color )
    {
        $this->set_prop( 'digit_color', $color );
    }

    /**
     * Returns headline visibility.
     * ::PROP
     *
     * @return mixed
     */

    public function getHeadlineVisibility()
    {
        $meta = 'headline_visibility';

        $value = $this->get_prop( $meta, false );
        if ( $value === C::NO || $value === C::YES ) {
            return $value;
        }

        $legacyMeta = 'display_headline';
        $legacyValue = filter_var( $this->get_prop_legacy( $legacyMeta ),
            FILTER_VALIDATE_BOOLEAN );
        $legacyValue = $legacyValue ? C::YES : C::NO;

        $this->set_prop( $meta, $legacyValue );

        $this->delete_prop( $legacyMeta );

        return $this->get_prop( $meta );
    }

    /**
     * Returns digit color.
     * Backward compat with older versions.
     * ::PROP
     *
     * @return mixed
     */
    public function getDigitColor()
    {
        $value = $this->get_prop( 'digit_color', false );
        if ( $value ) {
            return $value;
        }

        $legacy = $this->get_prop_legacy( 'text_color' );
        if ( !$legacy ) {
            return $this->digitColor;
        }

        $this->setDigitColor( $legacy );
        if ( !$this->get_prop( 'label_color', false ) ) {
            $this->setLabelColor( $legacy );
        }

        $this->delete_prop( 'text_color' );

        return $this->get_prop( 'digit_color' );
    }

    /**
     * ::PROP
     *
     * @param $color
     */
    public function setLabelColor( $color )
    {
        $this->set_prop( 'label_color', $color );
    }

    /**
     * Get label color
     * ::PROP
     *
     * @return mixed
     */
    public function getLabelColor()
    {
        $value = $this->get_prop( 'label_color', false );
        if ( $value ) {
            return $value;
        }

        $legacy = $this->get_prop_legacy( 'text_color' );
        if ( !$legacy ) {
            return $this->labelColor;
        }

        $this->setLabelColor( $legacy );
        if ( !$this->get_prop( 'digit_color', false ) ) {
            $this->setDigitColor( $legacy );
        }

        $this->delete_prop( 'text_color' );

        return $this->get_prop( 'label_color' );

    }

    /**
     *
     * Return timer digit size.
     * Backward comapt. with older versions.
     * ::PROP
     *
     * @return int
     * @since 1.2.4
     */
    public function getDigitSize()
    {
        $meta = 'digit_size';
        $value = $this->get_prop( $meta, false );
        if ( !empty( $value ) ) {
            return $value;
        }
        $legacy = $this->get_prop_legacy( 'text_size' );

        if ( empty( $legacy ) ) {
            return $this->digitSize;
        }

        $this->setDigitSize( $legacy );

        $this->delete_prop( 'text_size' );

        return $this->get_prop( 'digit_size' );
    }

    /**
     * Save timer digit size.
     * ::PROP
     *
     * @param $size
     */
    public function setDigitSize( $size )
    {
        $this->set_prop( 'digit_size', $size );
    }

    /**
     * Save timer label size.
     * ::PROP
     *
     * @param $size
     */
    public function setLabelSize( $size )
    {
        $this->set_prop( 'label_size', $size );
    }

    /**
     * ::PROP
     *
     * @param $size
     */
    public function setHeadlineSize( $size )
    {
        $this->set_prop( 'headline_size', $size );
    }

    /**
     * Returns evergreen duration.
     * ::PROP
     *
     * @return array
     */
    public function getDuration()
    {
        $default = [ 0, 0, 0, 0 ];
        $duration = $this->get_prop( 'duration' );
        if ( is_array( $duration ) ) {
            $duration = array_merge( $duration, $default );

            return array_map( 'intval', $duration );
        }

        return $default;
    }

    /**
     * ::PROP
     *
     * @param $value
     */
    public function setRecurringDuration( $value )
    {
        $this->set_prop( '_hurryt_recurring_duration', $value );
    }


    /**
     * Returns evergreen duration.
     *
     * @return array
     */
    public function getRecurringDuration()
    {
        $default = [ 0, 0, 0, 0 ];
        $duration = $this->get_prop( '_hurryt_recurring_duration', false );
        if ( is_array( $duration ) ) {
            return array_map( 'intval', array_merge( $duration, $default ) );
        }

        return $default;
    }

    /**
     * Returns actions array.
     *
     * @return array
     */
    public function getActions()
    {
        $legacy = $this->get_prop_legacy( 'end_action' );
        if ( $legacy ) {
            $redirectUrl = $this->get_prop_legacy( 'redirect_url' );
            $actions = [
                [
                    'id' => intval( $legacy ),
                    'redirectUrl' => $redirectUrl,
                ],
            ];
            $this->set_prop( 'actions', $actions );
            $this->delete_prop( 'end_action' );
            $this->delete_prop( 'redirect_url' );

            return $this->mergeActions( $actions );
        }

        return $this->mergeActions( $this->get_prop( 'actions' ) );
    }

    private function mergeActions( $actions )
    {
        $defaults = [
            [
                'id' => C::ACTION_NONE,
                'redirectUrl' => '',
                'message' => '',
                'wcStockStatus' => '',
            ],
        ];

        if ( count( $actions ) === 0 ) {
            return $defaults;
        }

        return array_map( function ( $action ) use ( $defaults ) {
            $action[ 'id' ] = (int)$action[ 'id' ];

            return array_merge( $defaults[ 0 ], $action );
        }, $actions );
    }

    /**
     * Returns evergreen duration in seconds.
     *
     * @param array $duration
     *
     * @return int
     */
    public function duration_to_sec( $duration = [] )
    {
        list( $d, $h, $m, $s ) = empty( $duration ) ? $this->getDuration() : $duration;

        return $d * DAY_IN_SECONDS +
            $h * HOUR_IN_SECONDS +
            $m * MINUTE_IN_SECONDS +
            $s;
    }

    /**
     * Returns end datetime.
     *
     * @return string
     */
    public function getEndDatetime()
    {
        return $this->get_prop( 'end_datetime' ) ?: $this->defaultEndDatetime();
    }

    /**
     * Return true if the recurrence is expired.
     *
     * @return bool
     */
    public function is_recurring_expired()
    {
        $deadline = $this->get_current_recurrence_end_date();
        if ( !( $deadline instanceof Carbon ) ) {
            return false;
        }

        $now = Carbon::now( hurryt_tz() );

        return $now->isAfter( $deadline );
    }

    /**
     * Calculate next recurrence.
     *
     * @return mixed
     */
    function get_time_to_next_recurrence()
    {
        $this->loadSettings();
        $interval = absint( $this->recurringInterval );

        if ( $this->recurringFrequency === C::RECURRING_DAILY ) {
            $frequencyInSeconds = DAY_IN_SECONDS;
        } elseif ( $this->recurringFrequency === C::RECURRING_WEEKLY ) {
            $frequencyInSeconds = WEEK_IN_SECONDS;

        } elseif ( $this->recurringFrequency === C::RECURRING_HOURLY ) {
            $frequencyInSeconds = HOUR_IN_SECONDS;

        } else {
            $frequencyInSeconds = MINUTE_IN_SECONDS;
        }

        $duration = $this->duration_to_sec( $this->getRecurringDuration() );

        return max( 0, ( $frequencyInSeconds * $interval ) - $duration );
    }

    /**
     * Return true if the current campaign can recur today.
     *
     * @return bool
     */
    public function can_recur_today()
    {
        $this->loadSettings();
        $day = absint( Carbon::now( hurryt_tz() )->format( 'w' ) );
        $recur = in_array( $day, array_map( 'absint', $this->recurringDays ) );

        return apply_filters( 'hurryt_recur_today', $recur, $this->get_id() );
    }

    /**
     * Return current recurrence date
     *
     * @return \Carbon\CarbonInterface|null
     * @throws \Exception
     */
    public function get_current_recurrence_start_date()
    {
        try {
            $this->loadSettings();
            $dtStart = Carbon::parse( $this->recurringStartTime, hurryt_tz() );
            $now = Carbon::now( hurryt_tz() );

            $range = CarbonPeriod::since( $dtStart );
            $interval = absint( $this->recurringInterval );

            if ( $this->recurringFrequency === C::RECURRING_DAILY ) {
                $range->days( $interval );
            } elseif ( $this->recurringFrequency === C::RECURRING_WEEKLY ) {
                $range->weeks( $interval );
            } elseif ( $this->recurringFrequency === C::RECURRING_HOURLY ) {
                $range->hours( $interval );
            } else {
                $range->minutes( $interval );
            }

            // Recurs forever
            if ( absint( $this->recurringEnd ) === C::RECURRING_END_NEVER ) {
                $range->until( $now );
                // Recurs until
            } elseif ( absint( $this->recurringEnd ) === C::RECURRING_END_TIME ) {
                $range->until( Carbon::parse( $this->recurringUntil, hurryt_tz() ) );

                $range->addFilter( function ( $date ) use ( $now ) {
                    /**
                     * @var Carbon $date
                     */
                    return $date->isBefore( $now );
                } );

                // Recurs N times.
            } elseif ( absint( $this->recurringEnd ) === C::RECURRING_END_OCCURRENCES ) {

                // Virtual endDate
                $range->until( Carbon::now( hurryt_tz() )->addCenturies( 1 ) );

                // Limit to the number of occurences in `$this->recurringCount`
                $range->setRecurrences( absint( $this->recurringCount ) );

                // Set the endDate to the last occurence date
                $range->setEndDate( $range->last() );
                $range->addFilter( function ( $date ) use ( $now ) {

                    /**
                     * @var Carbon $date
                     */
                    return $date->isBefore( $now );
                } );

            }

            return $range->last();

        } catch ( \Exception $e ) {
            return null;
        }
    }

    /**
     * Get the next/current deadline based on the current date.
     *
     * @return null|Carbon
     */
    public function get_current_recurrence_end_date()
    {
        $date = $this->get_current_recurrence_start_date();
        if ( !$date ) {
            return null;
        }
        $date = clone Carbon::instance( $date );

        $duration = $this->duration_to_sec( $this->getRecurringDuration() );

        $date->addSeconds( $duration );

        return $date;
    }

    /**
     * Returns compaign publish datetime.
     *
     * @return mixed
     */
    public function getStartTimestamp()
    {
        return get_the_date( $this->id );
    }

    /**
     * Returns position in WooCommerce product page.
     * ::PROP
     *
     * @return mixed
     */
    public function getWcPosition()
    {
        $legacy = $this->get_prop_legacy( 'position' );

        if ( !$legacy ) {
            return $this->get_prop( 'wc_position' );
        }
        $this->set_prop( 'wc_position', $legacy );
        $this->delete_prop( 'position' );

        return $legacy;
    }

    private function get_prop_legacy( $name )
    {
        return get_post_meta( $this->id, $name, true );
    }

    /**
     * Returns true if WooCommerce integration is enabled.
     * ::PROP
     *
     * @return mixed
     */
    public function getWcEnable()
    {
        $value = $this->get_prop( 'wc_enable', false );

        if ( $value === C::YES || $value === C::NO ) {
            return $value;
        }

        $legacy = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
        if ( $legacy ) {
            $this->set_prop( 'wc_enable', C::YES );
        } else {
            $this->set_prop( 'wc_enable', C::NO );
        }

        return $this->get_prop( 'wc_enable' );
    }

    /**
     * Returns true if label should be displayed.
     *
     * @return mixed
     */
    public function getLabelVisibility()
    {
        $meta = 'label_visibility';
        $value = $this->get_prop( $meta, false );
        if ( $value === C::NO || $value === C::YES ) {
            return $value;
        }

        $legacy = filter_var( $this->get_prop_legacy( 'display_labels' ),
            FILTER_VALIDATE_BOOLEAN );
        $legacy = $legacy ? C::YES : C::NO;

        $this->set_prop( $meta, $legacy );

        $this->delete_prop( 'display_labels' );

        return $this->get_prop( $meta );

    }

    /**
     *
     * Returns restart type.
     *
     * @return int
     */
    public function getRestart()
    {
        return $this->get_prop( 'restart' ) ?: C::RESTART_NONE;
    }

    /**
     * Returns WooCommerce products selection type.
     *
     * @return string
     */
    public function getWcProductsSelectionType()
    {
        $legacy = $this->get_prop_legacy( 'products_type' );
        if ( !$legacy ) {
            return $this->get_prop( 'wc_products_selection_type' );
        }
        $this->set_prop( 'wc_products_selection_type', $legacy );
        $this->delete_prop( 'products_type' );

        return $legacy;
    }

    /**
     * Delete setting item.
     *
     * @param $name
     */
    public function delete_prop( $name )
    {
        delete_post_meta( $this->id, $name );
    }

    /**
     * Returns products selection IDs.
     * ::PROP
     *
     * @return array
     */
    public function getWcProductsSelection()
    {
        $legacy = $this->get_prop_legacy( 'products' );
        if ( !$legacy ) {
            return $this->get_prop( 'wc_products_selection' );
        }
        $this->set_prop( 'wc_products_selection', $legacy );
        $this->delete_prop( 'products' );

        return $legacy;
    }

    /**
     * Store custom labels.
     * ::PROP
     *
     * @param $labels
     */
    public function setLabels( $labels )
    {
        $labels = array_merge( $this->labels, array_filter( $labels ) );
        update_post_meta( $this->id, 'labels', $labels );
    }

    /**
     * Returns true if compaign can be published.
     *
     * @return bool
     */
    public function is_active()
    {
        return get_post_status( $this->id ) === "publish"
            || get_post_status( $this->id ) === "future";

    }

    /**
     * Returns trus if the compaign is scheduled.
     *
     * @return bool
     */
    public function is_scheduled()
    {
        $scheduled = get_post_status( $this->get_id() ) === "future";
        if ( $scheduled ) {
            return true;
        }

        if ( $this->is_recurring() ) {
            $start_date = Carbon::parse( $this->recurringStartTime, hurryt_tz() );
            $now = Carbon::now( hurryt_tz() );
            if ( $now->isBefore( $start_date ) ) {
                return true;
            }
        }

        return $scheduled;
    }

    /**
     * Returns true if fixed campaign datetime is expired.
     *
     * @param string|null $date
     *
     * @return bool
     */
    public function is_one_time_expired( $date = null )
    {
        $endDate = $date === null ? date_create( $this->endDatetime ) : $date;
        $today = date_create( current_time( "mysql" ) );

        return $endDate < $today;
    }

    public function is_sticky_dismissed()
    {
        return isset( $_COOKIE[ CookieDetection::COOKIE_PREFIX . $this->get_id() . '_dismissed' ] )
            && $this->stickyBarDismissible === C::YES
            && $this->enableSticky === C::YES;
    }

    /**
     * Returns compaign template wrapper.
     *
     * @param string
     *
     * @return string
     */
    public function wrap_template( $content )
    {
        $campaignBuilder = new CampaignBuilder( $this );

        return $campaignBuilder->build( $content );
    }

    /*
     * Returns campaign template content.
     */
    public function build_template()
    {
        $campaignBuilder = new CampaignBuilder( $this );

        return $campaignBuilder->template();
    }

    /**
     * ::PROP
     *
     * @param $value
     */
    public function setWcConditions( $value )
    {
        $this->set_prop( '_hurryt_wc_conditions', !empty( $value ) ? $value : [] );
    }

    /**
     * ::PROP
     *
     * @return mixed
     */
    public function getWcConditions()
    {
        return $this->get_prop( '_hurryt_wc_conditions', false );
    }


    public function is_running()
    {
        $no = !$this->is_active()
            || $this->is_scheduled()
            || !$this->show_sticky_on_page( hurryt_current_page_id() )
            || $this->is_sticky_dismissed()
            || ( $this->is_recurring() && !$this->can_recur_today() )
            || ( $this->is_recurring() && $this->get_current_recurrence_end_date() === null );

        return !$no;
    }

    /**
     * Check if recurring or onetime campaign is expired.
     *
     * @return bool
     */
    public function is_expired()
    {
        return ( $this->is_one_time() && $this->is_one_time_expired() )
            || ( $this->is_recurring() && $this->is_recurring_expired() );
    }

    public function get_mode_slug(){
        switch($this->mode){
            case C::MODE_EVERGREEN:
                return 'evergreen';
                case C::MODE_RECURRING:
                return 'recurring';
                case C::MODE_REGULAR:
                return 'one_time';
        }
    }
}
