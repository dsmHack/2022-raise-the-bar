<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/*
Plugin Name: DSM Hack 2022
Plugin URI: dsmhack.org
description: DSM Hack 2022 Plugin
Version: 1.0
Author: DSM Hack
License: GPL2
*/


// Populates all Gravity Forms fields with the CSS class of 'populate-volunteer-names'
add_filter('gform_pre_render', 'populate_volunteer_names');
add_filter('gform_admin_pre_render', 'populate_volunteer_names');
function populate_volunteer_names($form)
{
    foreach ($form['fields'] as &$field) {

        if (strpos($field->cssClass, 'populate-volunteer-names') === false) {
            continue;
        }

        $volunteers = get_volunteers();
        $choices = array();
        foreach ($volunteers as $volunteer) {
            $choices[] = array('text' => $volunteer->name, 'value' => $volunteer->id);
        }

        $field->placeholder = 'Choose your name';
        $field->choices = $choices;

    }

    return $form;
}

function get_volunteers()
{
    return $GLOBALS['wpdb']->get_results(
		"SELECT DISTINCT CONCAT_WS(' ', `wp_zbs_contacts`.`zbsc_fname`, `wp_zbs_contacts`.`zbsc_lname`) as name, `wp_zbs_contacts`.id FROM `wp_zbs_contacts` INNER JOIN `rbi_volunteer_availability` ON `rbi_volunteer_availability`.`person_id` = `wp_zbs_contacts`.id ORDER BY name asc"
	);
}

// Grab user id from wp_users on registration
add_action( 'um_registration_complete', 'my_registration_complete', 10, 2 );
function my_registration_complete( $user_id, $args ) {
	create_contact($args, $user_id);
}

if ( ! function_exists('create_contact')) {
   function create_contact ( $args, $user_id )  {
	  $sub = $args['submitted'];
      global $wpdb; 
	  $table_name = $wpdb->prefix . 'zbs_contacts'; 
	  $date = new DateTime();
	  $timestamp = $date->getTimeStamp();
	  $wpdb->insert($table_name, array('zbsc_fname' => $sub['first_name'], 'zbsc_lname' => $sub['last_name'],
									   'zbsc_email' => $sub['user_email'],
									   'zbs_site' => 1,'zbs_team' => 1, 'zbs_owner' => -1, 'zbsc_status' => "Customer", 'zbsc_lastcontacted' => -1,
									   'zbsc_wpid' => $user_id,'zbsc_created' => $timestamp)); 
   }
}

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

// Create series on series form entry
add_action('gform_after_submission_4', 'create_series', 10, 2);
function create_series($entry, $form)
{
    $name = rgar( $entry, '1' );
    $sku = rgar( $entry, '3' );
    $description = rgar( $entry, '5');

    $GLOBALS['wpdb']->insert("rbi_series", array(
        'name'=>$name,
        'sku'=> $sku,
        'description'=>$description,
    ), array(
        '%s',
        '%s',
        '%s',
    ));
}

// Gravity Forms - Populate Series
add_filter('gform_pre_render', 'populate_series');
add_filter('gform_admin_pre_render', 'populate_series');
function populate_series($form)
{
    $form_ids = [5];
    if (!in_array($form['id'], $form_ids)) {
        return $form;
    }

    foreach ($form['fields'] as &$field) {

        if ($field->id != 9) {
            continue;
        }

        $seriesArr = get_series();
        $choices = array();
        foreach ($seriesArr as $series) {
			$text = $series->name . ' (SKU: ' . $series->sku . ')';
            $choices[] = array('text' => $text, 'value' => $series->id);
        }

        $field->placeholder = 'Choose your series';
        $field->choices = $choices;

    }

    return $form;
}

function get_series()
{
    return $GLOBALS['wpdb']->get_results("SELECT * FROM `rbi_series` ORDER BY id DESC");
}

// Create event on event form entry
add_action('gform_after_submission_9', 'create_event', 10, 2);
function create_event($entry, $form)
{

    $name = rgar($entry, '1');
    $addressLine1 = rgar($entry, '10.1');
    $addressLine2 = rgar($entry, '10.2');
    $city = rgar($entry, '10.3');
    $state = rgar($entry, '10.4');
    $zip = rgar($entry, '10.5');
    $date = rgar($entry, '4');
    $start_time = rgar($entry, '6');
    $end_time = rgar($entry, '5');
    $series_id = rgar($entry, '9');

    $data = array('name' => $name, 'date' => $date, 'start_time' => $start_time, 'end_time' => $end_time, 'address_1' => $addressLine1, 'address_2' => $addressLine2, 'address_city' => $city, 'address_state' => $state, 'series_id' => $series_id, 'address_zip' => $zip);

    $GLOBALS['wpdb']->insert("rbi_event", $data, array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'));

}

// Gravity Forms - Populate events
add_filter('gform_pre_render', 'populate_events');
add_filter('gform_admin_pre_render', 'populate_events');
function populate_events($form)
{
    foreach ($form['fields'] as &$field) {

        if (strpos($field->cssClass, 'populate-events') === false) {
            continue;
        }

        $events = get_events();
        $choices = array();
        foreach ($events as $event) {
			$theDate = new DateTime($event->date);
			$theStartTime = new DateTime($event->{'start_time'});
            $choices[] = array('text' => $event->name . ' (' . $theDate->format('m-d-Y') . ' ' . $theStartTime->format('h:i') . ')', 'value' => $event->id);
        }

        $field->placeholder = 'Choose your event';
        $field->choices = $choices;
    }

    return $form;
}

function get_events()
{
    return $GLOBALS['wpdb']->get_results("SELECT * FROM `rbi_event` ORDER BY date desc, start_time desc");
}

// Gravity Forms - Create volunteer check-in
add_action('gform_after_submission_6', 'create_volunteer_event', 10, 2);
function create_volunteer_event($entry, $form)
{
    $event_id = rgar( $entry, '1' );
    $person_id = rgar( $entry, '5' );
    $start_time = rgar( $entry, '4');
	
	$field       = GFFormsModel::get_field( $form, '7' );
	$field_value = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	$tags = explode (",", $field_value);

    $GLOBALS['wpdb']->insert("rbi_volunteer_event", array(
        'event_id'=>$event_id,
        'person_id'=>$person_id,
        'start_time'=>$start_time,
    ), array(
        '%d',
        '%d',
        '%s',
    ));
	
	$volunteer_event_id = $GLOBALS['wpdb']->insert_id;
	
	foreach($tags as $tag) {
		$GLOBALS['wpdb']->insert("rbi_volunteer_event_tag", array(
        'volunteer_event_id'=>$volunteer_event_id,
        'tag_id'=>$tag
    ), array(
        '%d',
        '%d',
    ));
	}
}

// Gravity Forms - Populate participant names
//Populates all Gravity Forms fields with the CSS class of 'populate-participant-names'
add_filter('gform_pre_render', 'populate_participant_names');
add_filter('gform_admin_pre_render', 'populate_participant_names');
function populate_participant_names($form)
{
    foreach ($form['fields'] as &$field) {

        if (strpos($field->cssClass, 'populate-participant-names') === false) {
            continue;
        }

        $volunteers = $GLOBALS['wpdb']->get_results("SELECT DISTINCT CONCAT_WS(' ', c.`zbsc_fname`, c.`zbsc_lname`) as name, c.id FROM `wp_zbs_contacts` c ORDER BY name asc;");
        $choices = array();
        foreach ($volunteers as $volunteer) {
            $choices[] = array('text' => $volunteer->name, 'value' => $volunteer->id);
        }

        $field->placeholder = 'Choose your name';
        $field->choices = $choices;

    }

    return $form;
}

// Gravity Forms - Create participant check-in
add_action('gform_after_submission_6', 'create_participant_event', 10, 2);
function create_participant_event($entry, $form)
{
    $event_id = rgar( $entry, '1' );
    $person_id = rgar( $entry, '3' );
	$referral_id = rgar( $entry, '4' );
	$referral_notes = rgar( $entry, '6' );

    $GLOBALS['wpdb']->insert("rbi_participant_event", array(
        'event_id'=>$event_id,
        'person_id'=>$person_id,
        'referral_id'=>$referral_id,
		'referral_notes'=>$referral_notes
    ), array(
        '%d',
        '%d',
        '%s',
		'%d',
		'%s'
    ));
}

// Checkout Multi-step
add_action( 'gform_pre_render', 'pre_render_checkout');
function pre_render_checkout( $form ){
	// This handles (or should handle) populating the second step of the checkout form
	// with the People/users that have checked in.
	
	if ( $form["id"] == 7 ) {
		//check what page you are on
		$current_page = GFFormDisplay::get_current_page(7);
		if ($current_page == 2)
		{
			$eventId = rgpost("input_1");
			echo '<pre>';
			echo var_dump($_POST);
			echo '</pre>';
			echo "EVENT ID ".$eventId;
			
			
		}
	}
	
	return $form;
}

// Gravity Forms - Create volunteer availability
add_action('gform_after_submission_10', 'create_volunteer_availability', 10, 2);
function create_volunteer_availability($entry, $form)
{
    $volunteer_id = rgar( $entry, '4' );
	
    $mon_start_time = rgar( $entry, '10');
    $mon_end_time = rgar( $entry, '11');
	
    $tue_start_time = rgar( $entry, '12');
    $tue_end_time = rgar( $entry, '13');
	
    $wed_start_time = rgar( $entry, '14');
    $wed_end_time = rgar( $entry, '23');
	
    $thu_start_time = rgar( $entry, '22');
    $thu_end_time = rgar( $entry, '21');
	
    $fri_start_time = rgar( $entry, '20');
    $fri_end_time = rgar( $entry, '19');
	
    $sat_start_time = rgar( $entry, '18');
    $sat_end_time = rgar( $entry, '17');
	
    $sun_start_time = rgar( $entry, '16');
    $sun_end_time = rgar( $entry, '15');

    insert_availability_record($volunteer_id, "Mon", $mon_start_time, $mon_end_time);
	insert_availability_record($volunteer_id, "Tue", $tue_start_time, $tue_end_time);
    insert_availability_record($volunteer_id, "Wed", $wed_start_time, $wed_end_time);
    insert_availability_record($volunteer_id, "Thu", $thu_start_time, $thu_end_time);
    insert_availability_record($volunteer_id, "Fri", $fri_start_time, $fri_end_time);
    insert_availability_record($volunteer_id, "Sat", $sat_start_time, $sat_end_time);
    insert_availability_record($volunteer_id, "Sun", $sun_start_time, $sun_end_time);
}

function insert_availability_record($volunteer_id, $day_of_week, $start_time, $end_time)
{
    $GLOBALS['wpdb']->insert("rbi_volunteer_availability", array(
        'person_id'=>$volunteer_id,
        'day'=>$day_of_week,
        'start_time'=>$start_time,
        'end_time'=>$end_time
    ), array(
        '%d',
        '%s',
        '%s',
		'%s'
    ));
}


// Gravity Forms - Populate tags
add_filter('gform_pre_render', 'populate_tags');
add_filter('gform_admin_pre_render', 'populate_tags');
function populate_tags($form)
{
    foreach ($form['fields'] as &$field) {

        if (strpos($field->cssClass, 'populate-tags') === false) {
            continue;
        }

        $tags = get_tags_from_table();
        $choices = array();
        foreach ($tags as $tag) {
            $choices[] = array('text' => $tag->name, 'value' => $tag->id);
        }

        $field->placeholder = 'Choose your tags';
        $field->choices = $choices;
    }

    return $form;
}

function get_tags_from_table()
{
    return $GLOBALS['wpdb']->get_results("SELECT * FROM `rbi_tag`");
}