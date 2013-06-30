<?php
/*
Plugin Name: oDesk Testimonial
Plugin URI: http://www.julianjosephs.com/odesk_testimonial
Description: This plugin allows users to easily display oDesk testimonials on their site
Author: Julian Josephs
Version: 1.0
Author URI: http://www.julianjosephs.com/


Copyright 2012  Julian Josephs  (email : josephs.julian@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


// registers all the options in the database
function jinnovate_init()
{
    register_setting('jinnovate_odesk_options', 'jinnovate_odesk_key');
    register_setting('jinnovate_odesk_options', 'jinnovate_odesk_num_of_testimonials');
    register_setting('jinnovate_odesk_options', 'jinnovate_odesk_min_score');
    register_setting('jinnovate_odesk_options', 'jinnovate_odesk_text_limit');
    register_setting('jinnovate_odesk_options', 'jinnovate_odesk_error_message');
}

add_action('admin_init', 'jinnovate_init');


// generates the options page for the admin area
function jinnovate_odesk_option_page()
{
    global $_REQUEST;
    ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            
            <h2>oDesk Testimonial Options</h2>
            <p>Welcome to Jinnovate's oDesk Testimonial Plugin. To use this plugin, enter your profile key in the field below. <br />
            To get your profile key, you must first view your profile on oDesk. Go to the URL section in your browser where you will see this <br />
            https://www.odesk.com/users/<b style="color:red;">~~xxxxxxxxxxxxxxxx</b>. Copy the highlighted section and paste it in the <br />
            oDesk Profile Key below. Note, you must include <b style="color:red;">~~</b> in the key below.</p> <br /><br />
            
            <form action="options.php" method="post" id="jinnovate-odesk-options-form">
                <?php settings_fields('jinnovate_odesk_options'); ?>
                
                <label for="jinnovate_odesk_key">oDesk Profile Key: </label>
                <input type="text" id="jinnovate_odesk_key" name="jinnovate_odesk_key"
                       value="<?php echo esc_attr(get_option('jinnovate_odesk_key')); ?>" />
                    
                <br />
                <label for="jinnovate_odesk_min_score">Minimum Feedback Score: </label>
                <span id="odesk_slider"></span>
                <input type="text" id="jinnovate_odesk_min_score" name="jinnovate_odesk_min_score" readonly="readonly" maxlength="1" style="width:20px;" 
                       value="<?php echo esc_attr(get_option('jinnovate_odesk_min_score')); ?>" />
                       
                <br />
                <label for="jinnovate_odesk_text_limit">Text Limit <em>(e.g. 500)</em>: </label>
                <input type="text" id="jinnovate_odesk_text_limit" name="jinnovate_odesk_text_limit" 
                       value="<?php echo esc_attr(get_option('jinnovate_odesk_text_limit')); ?>" />
                    
                <br />
                <label for="jinnovate_odesk_error_message">Default Error Message: </label>
                <input type="text" id="jinnovate_odesk_error_message" name="jinnovate_odesk_error_message"
                       value="<?php echo esc_attr(get_option('jinnovate_odesk_error_message')); ?>" />
                       
                <p><input type="submit" name="submit" value="Update" /></p>
            </form>
            
        </div>
    <?php
}


// scripts used for the admin
function jinnovate_odesk_scripts()
{
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-core' );
    wp_register_script( 'jquery-ui-slider', getURL_jinnovate(). 'jquery-ui-1.8.16.custom/js/jquery-ui-1.8.16.custom.min.js');
    wp_enqueue_script( 'jquery-ui-slider' );
    wp_enqueue_script( 'odesk_testimonial', getURL_jinnovate() . 'odesk_testimonial.js' );
}


// styles used for the admin
function jinnovate_odesk_styles()
{
    wp_register_style( 'jquery-ui-slider-css', getURL_jinnovate(). 'jquery-ui-1.8.16.custom/css/no-theme/jquery-ui-1.8.16.custom.css');
    wp_enqueue_style( 'jquery-ui-slider-css' );
    wp_enqueue_style('odesk_css', getURL_jinnovate().'odesk_testimonial.css');
}


// adds the options page to the menu
function jinnovate_odesk_plugin_menu()
{
    $odesk_testi_name = add_options_page('oDesk Testimonial Options', 'oDesk Testimonials', 'manage_options', 'jinnovate-odesk-options', 'jinnovate_odesk_option_page');
    
    
    add_action('admin_print_styles-' . $odesk_testi_name, 'jinnovate_odesk_styles' );
    add_action('admin_print_scripts-' . $odesk_testi_name, 'jinnovate_odesk_scripts');
}

add_action('admin_menu', 'jinnovate_odesk_plugin_menu');


// generates the oDesk testimonial on a Page or Post
function jinnovate_odesk_testimonial()
{
    echo $provider_profile = get_odesk_provider_profile(get_option("jinnovate_odesk_key"));
    
    if($provider_profile){
        $minimum_score = get_option('jinnovate_odesk_min_score');
        $text_limit = get_option('jinnovate_odesk_text_limit');
        
        $feedback_star = '<img src="' . getURL_jinnovate() .'star.png" alt="oDesk Feedback Star" />';
        
        $testimonial = '<div id="jinnovate_odesk_wrap">';
        
        foreach ($provider_profile->profile->assignments->hr->job as $details) {
            if($details->as_status == 'Closed' && $details->feedback->score >= $minimum_score)
            {
                $feedback =  $details->feedback->comment;
                $feedback_score = round($details->feedback->score);
                
                if($feedback != '' && $feedback_score != '')
                {
                    $count = 0;
                    $testimonial .= '<div class="jinnovate_odesk_testimonial">';
                        $testimonial .= '<h3 class="jinnovate_odesk_t_title">'.$details->as_opening_title.'</h3>';
                        while($count < $feedback_score)
                        {
                            $testimonial .= $feedback_star;
                            $count++;
                        }
                        
                        if($text_limit > 0 && strlen($feedback) > $text_limit)
                        {
                            $feedback = substr($feedback, 0, $text_limit);
                            $feedback .= '...';
                        }
                        $testimonial .= '<p class="jinnovate_odesk_t_feedback">'.$feedback.'</p>';
                    $testimonial .= '</div>';
                }
            }
        }
        
        foreach ($provider_profile->profile->assignments->fp->job as $details) {
            if($details->as_status == 'Closed' && $details->feedback->score >= $minimum_score)
            {
                $feedback =  $details->feedback->comment;
                $feedback_score = round($details->feedback->score);
                
                if($feedback != '' && $feedback_score != '')
                {
                    $count = 0;
                    $testimonial .= '<div class="jinnovate_odesk_testimonial">';
                        $testimonial .= '<h3 class="jinnovate_odesk_t_title">'.$details->as_opening_title.'</h3>';
                        while($count < $feedback_score)
                        {
                            $testimonial .= $feedback_star;
                            $count++;
                        }
                        
                        if($text_limit > 0 && strlen($feedback) > $text_limit)
                        {
                            $feedback = substr($feedback, 0, $text_limit);
                            $feedback .= '...';
                        }
                        $testimonial .= '<p class="jinnovate_odesk_t_feedback">'.$feedback.'</p>';
                    $testimonial .= '</div>';
                }
            }
        }
        
        $testimonial .= "</div>";
        
        echo $testimonial;
    }
    else{
        $error_msg = get_option('jinnovate_odesk_error_message');
        echo '<p class="jinnovate_odesk_error">' . ($error_msg ? $error_msg : 'something went wrong :-(') . '</p>';
    }
}

add_shortcode('odesk_testimonials', 'jinnovate_odesk_testimonial');


// gets profile data through the oDesk API
function get_odesk_provider_profile($key) {
    $url = "http://www.odesk.com/api/profiles/v1/providers/".$key.".xml";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);	
    curl_close($ch);
    
    if ($status >= 200 && $status < 300) {	
            $doc = new SimpleXmlElement($data, LIBXML_NOCDATA);
            return $doc;
    }else{
            return false;
    }
}


// returns a url to the plugin folder
function getURL_jinnovate() {
    return WP_CONTENT_URL.'/plugins/'.basename(dirname(__FILE__)) . '/';
}


// css file used for both the front and backend
function add_jinnovate_odesk_style() {
    wp_enqueue_style('odesk_css', getURL_jinnovate().'odesk_testimonial.css');
}

add_action('wp_print_styles', 'add_jinnovate_odesk_style');


// generates the oDesk testimonial in widget area
function jinnovate_odesk_testimonial_widget($limit, $read_more_link){
    echo $provider_profile = get_odesk_provider_profile(get_option("jinnovate_odesk_key"));
    $count_limit = 0;
    
    if($provider_profile){
        $minimum_score = get_option('jinnovate_odesk_min_score');
        $text_limit = get_option('jinnovate_odesk_text_limit');
        
        $feedback_star = '<img src="' . getURL_jinnovate() .'star-widget.png" alt="oDesk Feedback Star" />';
        
        $testimonial = '<div id="jinnovate_odesk_wrap">';
        
        foreach ($provider_profile->profile->assignments->hr->job as $details) {
            
            if($count_limit >= $limit)
            {
                break;
            }
            else{
                if($details->as_status == 'Closed' && $details->feedback->score >= $minimum_score)
                {
                    $feedback =  $details->feedback->comment;
                    $feedback_score = round($details->feedback->score);
                    
                    if($feedback != '' && $feedback_score != '')
                    {
                        $count = 0;
                        $testimonial .= '<div class="jinnovate_odesk_testimonial">';
                            $testimonial .= '<h3 class="jinnovate_odesk_t_title">'.$details->as_opening_title.'</h3>';
                            while($count < $feedback_score)
                            {
                                $testimonial .= $feedback_star;
                                $count++;
                            }
                            
                            if($text_limit > 0 && strlen($feedback) > $text_limit)
                            {
                                $feedback = substr($feedback, 0, $text_limit);
                                $feedback .= '...';
                            }
                            $testimonial .= '<p class="jinnovate_odesk_t_feedback">'.$feedback.'</p>';
                        $testimonial .= '</div>';
                        
                        $count_limit++;
                    }
                }
            }
        }
        
        foreach ($provider_profile->profile->assignments->fp->job as $details) {
            if($count_limit >= $limit)
            {
                break;
            }
            else{
                if($details->as_status == 'Closed' && $details->feedback->score >= $minimum_score)
                {
                    $feedback =  $details->feedback->comment;
                    $feedback_score = round($details->feedback->score);
                    
                    if($feedback != '' && $feedback_score != '')
                    {
                        $count = 0;
                        $testimonial .= '<div class="jinnovate_odesk_testimonial">';
                            $testimonial .= '<h3 class="jinnovate_odesk_t_title">'.$details->as_opening_title.'</h3>';
                            while($count < $feedback_score)
                            {
                                $testimonial .= $feedback_star;
                                $count++;
                            }
                            
                            if($text_limit > 0 && strlen($feedback) > $text_limit)
                            {
                                $feedback = substr($feedback, 0, $text_limit);
                                $feedback .= '...';
                            }
                            $testimonial .= '<p class="jinnovate_odesk_t_feedback">'.$feedback.'</p>';
                        $testimonial .= '</div>';
                        
                        $count_limit++;
                    }
                }
            }
        }
        
        if($read_more_link){
            $testimonial .= '<div class="jinnovate-widget-read-more"><a href="' . $read_more_link . '">Read More</a></div>';
        }
        
        $testimonial .= "</div>";
        
        echo $testimonial;
    }
    else{
        $error_msg = get_option('jinnovate_odesk_error_message');
        echo '<p class="jinnovate_odesk_error">' . ($error_msg ? $error_msg : 'something went wrong :-(') . '</p>';
    }    
}


class JinnovateOdeskTestimonials extends WP_Widget{
    
    function JinnovateOdeskTestimonials()
    {
        $widget_options = array(
          'classname' => 'jinnovate-odesk-testimonials',
          'description' => 'oDesk Testimonials Widget'
        );
        
        parent::WP_Widget('jinnovate_odesk_test', 'oDesk Testimonials', $widget_options);
    }
    
    function widget($args, $instance)
    {
        extract( $args, EXTR_SKIP );
        
        $title = ($instance['title']) ? $instance['title'] : 'A Simple Widget';
        $body = ($instance['limit_testimonials']) ? $instance['limit_testimonials'] : '0';
        $read_more = ($instance['read_more']) ? $instance['read_more'] : '';
        ?>
        
        <?php echo $before_widget; ?>
        <?php echo $before_title . $title . $after_title; ?>
        <p><?php jinnovate_odesk_testimonial_widget($instance['limit_testimonials'], $instance['read_more']); ?> </p>
        
        <?php
    }
    
    function form($instance)
    {
        ?>
        <label for="<?php echo $this->get_field_id('title');?>">
        Title:
        <input id="<?php echo $this->get_field_id('title');?>"
                         name="<?php echo $this->get_field_name('title');?>"
                         value="<?php echo esc_attr($instance['title']); ?>" />
        </label><br /><br />
        
        <label for="<?php echo $this->get_field_id('limit_testimonials');?>">
        Testimonial Limit <em>(e.g. 5)</em>:
        <input maxlength="4" style="width:40px;" id="<?php echo $this->get_field_id('limit_testimonials');?>"
                         name="<?php echo $this->get_field_name('limit_testimonials');?>"
                         value="<?php echo esc_attr($instance['limit_testimonials']); ?>" />
        </label><br /><br />
        
        <label for="<?php echo $this->get_field_id('read_more');?>">
        Read More Link:
        <input style="width:80px;" id="<?php echo $this->get_field_id('read_more');?>"
                         name="<?php echo $this->get_field_name('read_more');?>"
                         value="<?php echo esc_attr($instance['read_more']); ?>" />
        </label><br /><br />
        
        <?php
    }
}

// register oDesk Testimonial Widget
function jinnovate_odesk_testimonials_init()
{
    register_widget('JinnovateOdeskTestimonials');
}

add_action('widgets_init', 'jinnovate_odesk_testimonials_init');