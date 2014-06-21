<?php
class Class_Example {

    public function __construct()
    {
        $this->set_install_hooks();
    }

    /**
     * To set all actions, hooks and filters
     *
     */
    protected function set_install_hooks() {

        /* Runs when plugin is activated */
        register_activation_hook(__FILE__,'cbw_install'); 

        /* Runs on plugin deactivation*/
        register_deactivation_hook( __FILE__, 'cbw_remove' );

        add_shortcode('account-info', array($this, 'account_info_handler'));

    }   
}
?>
