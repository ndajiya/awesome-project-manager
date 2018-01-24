<?php 

class FusionPM_Activity {

    protected $table_name;
    
    function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'fpm_activities';
    }

    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new FusionPM_Activity();
        }

        return $instance;
    }

    public function get_formatted_date( $date ) {
        $date_format = get_option( 'date_format' );
        // $time_format = get_option( 'time_format' );

        // $datetime_format = $date_format.' '.$time_format;

        $given_date = date_create( $date );
        $formatted_date = date_format( $given_date, $date_format );

        return $formatted_date;
    }

    public function get_formatted_time( $date ) {
        // $date_format = get_option( 'date_format' );
        $time_format = get_option( 'time_format' );

        // $datetime_format = $date_format.' '.$time_format;

        $given_date = date_create( $date );
        $formatted_time = date_format( $given_date, $time_format );

        return $formatted_time;
    }

    public function create( $data ) {
        global $wpdb;

        $insert = $wpdb->insert( $this->table_name, $data );

        if ( $insert ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    public function delete( $activity_id ) {
        global $wpdb;

        $result = $wpdb->delete( 
            $this->table_name,
            array( 'ID' => $activity_id )
        );
        return $result;
    }

    public function delete_activities_by_column( $value, $column ) {

        global $wpdb;

        $result = $wpdb->get_results( "SELECT `ID` FROM {$this->table_name} WHERE {$column} = {$value}" );

        foreach ($result as $activity) {
            $isSuccess = $this->delete($activity->ID);
        }

        return $isSuccess;
    }

    public function delete_activities_by_columns( $value1, $column1, $value2, $column2 ) {

        global $wpdb;

        $result = $wpdb->get_results( "SELECT `ID` FROM {$this->table_name} WHERE {$column1} = {$value1} AND {$column2} = '{$value2}'" );

        foreach ($result as $activity) {
            $isSuccess = $this->delete($activity->ID);
        }

        return $isSuccess;
    }

    public function get_activity_count( $project_id ) {
        global $wpdb;

        $activity_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->table_name} WHERE `projectID` = {$project_id}" );

        return $activity_count;
    }

    public function get_project_activities( $project_id = NULL, $limit = NULL, $offset = NULL ) {

        global $wpdb;

        if ( !$limit ) {
            $limit = 25;
        }

        if ( !$offset ) {
            $offset = 0;
        }
        
        $data = $wpdb->get_results( "SELECT * FROM {$this->table_name} WHERE `projectID` = {$project_id} ORDER BY `ID` DESC", ARRAY_A);

        $temp = array();
        foreach ( $data as $element ) {
            $nameOfDay = date('D, j M Y', strtotime($element['created']));
            $element['avatar_url'] = get_avatar_url($element['userID'], array('size'=>32));
            $element['formatted_date'] = $this->get_formatted_date( $element['created'] );
            $element['formatted_time'] = $this->get_formatted_time( $element['created'] );
            $temp[$nameOfDay][] = $element;
        }

        $result = array();
        foreach ($temp as $day => $activities) {
            $result[] = array(
                'activity_date' => $day,
                'activities' => $activities
            );
        }

        return $result;
    }

}