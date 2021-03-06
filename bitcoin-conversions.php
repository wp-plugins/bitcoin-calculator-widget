<?php
/**
 * @package Bitcoin_Calculator_Widget
 */
/*
Plugin Name: Bitcoin Calculator Widget
Plugin URI: http://www.bitcoinvalues.net
Description: Bitcoin exchange rates converter / calculator
Author: csmicfool
Version: 1.0.0
Author URI: http://www.bitconvalues.net
*/

class btc_widget extends WP_Widget {

        /**
         * Sets up the widgets name etc
         */
        public function __construct() {
                // widget actual processes
                parent::__construct(
                        'btc_widget', // Base ID
                        __('Bitcoin Exchange Rate', 'text_domain'), // Name
                        array( 'description' => __( 'Current Bitcoin Exchange Rates and coversion tools for multiple currencies.', 'text_domain' ), ) // Args
                );
        }

        /**
         * Outputs the content of the widget
         *
         * @param array $args
         * @param array $instance
         */
        public function widget( $args, $instance ) {
                $title = apply_filters( 'widget_title', $instance['title'] );

                echo $args['before_widget'];
                if ( ! empty( $title ) )
                        echo $args['before_title'] . $title . $args['after_title'];
                // outputs the content of the widget<select name="currency" id="crsel">
                if ( ! wp_http_supports( array( 'ssl' ) ) ) {
                        // this server can't do https, display an error or handle it gracefully
                        echo 'Error Loading Widget: SSL Not Supported';
                        return;
}

                if(get_transient('btc_data')===false){

                        $response = wp_remote_get( 'https://blockchain.info/ticker' );
                        if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
                                // failed to get a valid response, handle this error
                                echo 'Error Loading Widget Data';
                                return;
                        }
                        $d = wp_remote_retrieve_body( $response );
                        set_transient('btc_data',$d,300);
                }

                if(!get_transient('btc_data')===false){
                        $d = get_transient('btc_data');
                }

                $j = json_decode($d);
                $o = get_object_vars($j);
?>
                <script type="text/javascript">
                        jQuery(document).ready(function () {
                                jQuery('#crsel').change(function(){
                                        update_exc();
                                });
                                jQuery('#btc_amt').keyup(function(){
                                        update_exc();
                                });
                        });
                        function update_exc(){
                                var t = jQuery('#crsel').find('option:selected');
                                console.log(t);
                                var s = t.data('symbol');
                                var v = jQuery('#crsel').val();
                                var b = jQuery('#btc_amt').val();
                                v = Math.round(v*b*100)/100;
                                jQuery('#up').html('<strong style="font-size:1.2em;position:relative;margin-right:.1em">'+s+'</strong>' + v.toString());
                        }
                </script>
<div style="border: 1px lightgrey solid; text-align:center;"><img src="/wp-content/plugins/Bitcoin-Calculator-Widget/bitcoin-calc.png" width="290" border="0">
<br><br><b>Enter Amount of BTC to Convert:</b><br><br>
<div style="text-align:center;">
<input type="text" name="btc_amt" id="btc_amt" value="1" size="4" style="width:108px;"/> <br>&nbsp;&nbsp; BTC =
        <span id="up"><?php echo '<strong style="font-size:1.2em;position:relative;margin-right:.1em">'.$j->USD->symbol.'</strong>'.round(ceil($j->USD->{'15m'}*100))/100; ?></span>
<br><br><b>Select Currency To Convert To:</b>
<br><br>
        <select name="currency" id="crsel" style="width:108px;"><?php
                foreach(array_keys($o) as $c){
                        ?>
                        <option data-symbol="<?= $o[$c]->{'symbol'} ?>" value="<?= round(ceil($o[$c]->{'15m'}*100))/100 ?>"><?= "(".$o[$c]->{'symbol'}.") ".$c ?></option>
                        <?php
                }
                ?>
                </select>
<br><br>
<div style="background-color:#EEEEEE">
<br> Bitcoin Exchange Calculator<br><br>
</div>
 </div></div><?php
                echo $args['after_widget'];
        }

        /**
         * Ouputs the options form on admin
         *
         * @param array $instance The widget options
         */
        public function form( $instance ) {
                // outputs the options form on admin
                if ( isset( $instance[ 'title' ] ) ) {
                        $title = $instance[ 'title' ];
                }
                else {
                        $title = __( 'Bitcoin Exchange Rate', 'text_domain' );
                }
                ?>
                <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
                </p>
        <p>
                Enjoying the widget?&nbsp; Why not make a donation?
            <!-- Place this html code where you want the widget -->
            <div style="float:left;padding-right:12px;">
                <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TBNG4S2TJU3N6" target="_blank">
                        <img border="0" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="PayPal - The safer, easier way to pay online!">
                </a>
                        </div>
            <div style="float:left;padding-top:2px;">
                <div class="bitcoin-button" data-address="16qT2iLQ7d5MiEkKWYau6mfRNHUFZ3NzHz" data-info="none" data-message="Leave a tip to support my work!"></div>

                <!-- Place this snippet somewhere after the last button -->
                <script type="text/javascript">
                var bitcoinwidget_init = { autoload: true, host: "//bitcoinwidget.appspot.com" };
                (function() {
                var x = document.createElement("script"); x.type="text/javascript"; x.async=true;
                x.src = "//wp-content/plugins/Bitcoin-Calculator-Widget/bitcoinwidget.js";
                var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(x,s);
                })();
                </script>
            </div>
 <div class="clear"></div>
        </p>
                <?php
        }

        /**
         * Processing widget options on save
         *
         * @param array $new_instance The new options
         * @param array $old_instance The previous options
         */
        public function update( $new_instance, $old_instance ) {
                // processes widget options to be saved
                $instance = array();
                $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

                return $instance;
        }
}

add_action( 'widgets_init', function(){
     register_widget( 'btc_widget' );
});
?>
