<?php
/*
Plugin Name: Word Filter Plugin
Description: Replaces the content you do not want to show on your site.
Version: 1.0
Author: Li Ming

*/

if(!defined('ABSPATH')) exit;

class WordFilter{
    function __construct()
    {
        add_action('admin_menu', [$this, 'AddMenuToAdminPage']);
        add_action('admin_init', [$this, 'RegisterSettings']);
        add_filter('the_content', [$this, 'FilterTheSiteContent']);
    }

    function RegisterSettings(){
        add_settings_section('WordFilterOptionsSection', null, null, 'word-filter-options');
        add_settings_field('replacement-value', 'Replacement content', [$this, 'ReplacementValueFieldHTML'], 'word-filter-options', 'WordFilterOptionsSection');
        register_setting('WordFilterOptionGroup', 'replacement-value');
    }

    function ReplacementValueFieldHTML(){
        ?>
        <input type="text" name="replacement-value" id="" value="<?php echo esc_attr(get_option('replacement-value')) ?>">
        <p>Leave blank to simply remove the filtered words.</p>
        <?php
    }

    function FilterTheSiteContent($content){
        $ToBeFilterWordsList = explode(',', get_option('words_to_filter'));
        $ToBeFilterWordsList = array_map('trim', $ToBeFilterWordsList);

        if(count($ToBeFilterWordsList)){
            return str_ireplace($ToBeFilterWordsList, (get_option('replacement-value')), $content);
        }
        
        return $content;
    }

    function AddMenuToAdminPage(){
        $mainPageHook = add_menu_page('words filter plugin', 'Word Filter', 'manage_options', 'words-filter-plugin',
                                      [$this, 'RenderWordFilterPage'], plugin_dir_url(__FILE__) . 'custom.svg', 100);
        //add_menu_page('words filter plugin','Word Filter' , 'manage_options', 'words-filter-plugin',[$this, 'RenderWordFilterPage'],'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0xMCAyMEMxNS41MjI5IDIwIDIwIDE1LjUyMjkgMjAgMTBDMjAgNC40NzcxNCAxNS41MjI5IDAgMTAgMEM0LjQ3NzE0IDAgMCA0LjQ3NzE0IDAgMTBDMCAxNS41MjI5IDQuNDc3MTQgMjAgMTAgMjBaTTExLjk5IDcuNDQ2NjZMMTAuMDc4MSAxLjU2MjVMOC4xNjYyNiA3LjQ0NjY2SDEuOTc5MjhMNi45ODQ2NSAxMS4wODMzTDUuMDcyNzUgMTYuOTY3NEwxMC4wNzgxIDEzLjMzMDhMMTUuMDgzNSAxNi45Njc0TDEzLjE3MTYgMTEuMDgzM0wxOC4xNzcgNy40NDY2NkgxMS45OVoiIGZpbGw9IiNGRkRGOEQiLz4KPC9zdmc+', 100);
        add_submenu_page('words-filter-plugin','Words to Filter Options', 'Filter', 'manage_options', 'words-filter-plugin', [$this, 'RenderWordFilterPage']);
        add_submenu_page('words-filter-plugin','Word Filter Options', 'Options', 'manage_options', 'word-filter-options', [$this, 'RenderOptionsSubPage']);
        add_action("load-{$mainPageHook}", [$this,'pluginAssets']);
    }

    function pluginAssets(){
        wp_enqueue_style('wordfilter_style', plugin_dir_url(__FILE__) . 'style.css');
    }

    function handleForm(){
        if (wp_verify_nonce($_POST['ourNonce'], 'saveFilterWords') AND current_user_can('manage_options')) {
            update_option('words_to_filter', sanitize_text_field($_POST['words_to_filter'])); ?>
            <div class="updated">
              <p>Your filtered words were saved.</p>
            </div>
        <?php }
        else { ?>
            <div class="error">
              <p>Sorry, you do not have permission to perform that action.</p>
            </div>
        <?php } 
    }

    function RenderWordFilterPage(){
        ?>        
        <div class="wrap">
            <h1>Word Filter</h1>
            <?php
            if(isset($_POST['submit']))
            {
                if ($_POST['submit'] == "Save changes")
                    $this->handleForm();
            }
             
            ?>            
            <form method="POST">                
                <?php wp_nonce_field('saveFilterWords', 'ourNonce') ?>
                <label for="words_to_filter"><p>Enter a <strong>comma-separated</strong> list of words to filter from your site's content</p></label>
                <div class="word-filter__flex-container">
                    <textarea name="words_to_filter" id="words_to_filter" placeholder="just, example, like, this"><?php echo esc_textarea(get_option('words_to_filter')); ?></textarea>
                </div>
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save changes">
            </form>
        </div>
        <?php
    }

    function RenderOptionsSubPage(){
        ?>
        <div class="wrap">
            <h1>Word Filter Options</h1>
            <form action="options.php" method="post">
            <?php 
                settings_errors();
                settings_fields('WordFilterOptionGroup');
                do_settings_sections('word-filter-options');
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

}

$wordfilterPlugin = new WordFilter();