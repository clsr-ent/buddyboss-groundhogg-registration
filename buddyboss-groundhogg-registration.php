<?php
/**
 * Plugin Name: BuddyBoss + Groundhogg Registration
 * Plugin URI:  https://github.com/clsr-ent/buddyboss-groundhogg-registration
 * Description: Adds checkboxes for Terms & Newsletter on BuddyBoss register page; maps them to Groundhogg fields, confirms email, etc.
 * Version:     1.0.0
 * Author:      Closrr.com
 * Author URI:  https://github.com/your-username
 * License:     GPL-2.0+
 * Text Domain: groundhogg
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // No direct access.
}

/**
 * 1) Output two checkboxes above the Turnstile (bp_before_registration_submit_buttons, priority=5).
 *    - If BuddyBoss already inserts an "I agree to Terms" checkbox, remove or modify the second block.
 */
add_action( 'bp_before_registration_submit_buttons', 'bb_groundhogg_add_registration_checkboxes', 5 );
function bb_groundhogg_add_registration_checkboxes() {
    ?>
    <!-- Newsletter Checkbox -->
    <div class="input-options checkbox-options">
        <div class="bp-checkbox-wrap">
            <input type="checkbox"
                   name="signup_newsletter"
                   id="signup_newsletter"
                   value="1"
                   class="bs-styled-checkbox"
                   <?php checked( ! empty( $_POST['signup_newsletter'] ), 1 ); ?> />
            <label for="signup_newsletter" class="option-label">
                <?php esc_html_e( 'Subscribe to our platform and product updates.', 'groundhogg' ); ?>
            </label>
        </div>
    </div>

    <!-- Terms-of-Service Checkbox -->
    <div class="input-options checkbox-options">
        <div class="bp-checkbox-wrap">
            <input type="checkbox"
                   name="legal_agreement"
                   id="legal_agreement"
                   value="1"
                   class="bs-styled-checkbox"
                   <?php checked( ! empty( $_POST['legal_agreement'] ), 1 ); ?> />
            <label for="legal_agreement" class="option-label">
                <?php esc_html_e( 'I agree to the Terms of Service and Privacy Policy.', 'groundhogg' ); ?>
            </label>
        </div>
    </div>
    <?php
}

/**
 * 2) On user registration, map the posted data to Groundhogg fields, set gdpr_consent & terms_agreement if checked,
 *    and trigger "email confirmed" if they subscribed.
 */
add_action( 'user_register', 'bb_groundhogg_on_user_registration' );
function bb_groundhogg_on_user_registration( $user_id ) {

    // Check if Groundhogg is active and function is available
    if ( ! function_exists( 'generate_contact_with_map' ) ) {
        return;
    }

    // Get WP User
    $wp_user = get_userdata( $user_id );
    if ( ! $wp_user ) {
        return;
    }

    // We'll store "accepted" in the GH fields if checkboxes are ticked
    $gdpr_consent  = ! empty( $_POST['signup_newsletter'] ) ? 'accepted' : '';
    $terms_agree   = ! empty( $_POST['legal_agreement'] )   ? 'accepted' : '';

    // Prepare data array for generate_contact_with_map
    // Keys match the "map" below. e.g. 'fname' => $wp_user->first_name
    $data = [
        'fname'           => $wp_user->first_name,
        'lname'           => $wp_user->last_name,
        'email_address'   => $wp_user->user_email,
        // map these two to GH meta fields
        'gdpr_consent'    => $gdpr_consent,
        'terms_agreement' => $terms_agree,
    ];

    // Prepare the map: array keys (above) => GH fields
    // For 'meta', we store them as meta keys in GH. For 'email', we store them as the contact's main email, etc.
    $map = [
        'fname'           => 'first_name',
        'lname'           => 'last_name',
        'email_address'   => 'email',
        'gdpr_consent'    => 'meta', // store in meta key "gdpr_consent"
        'terms_agreement' => 'meta', // store in meta key "terms_agreement"
    ];

    // Generate or update the contact
    $contact = \Groundhogg\generate_contact_with_map( $data, $map );
    if ( ! $contact ) {
        return;
    }

    // Optionally, if you want to apply a tag to newsletter subscribers, do it here:
    if ( $gdpr_consent === 'accepted' ) {
        $contact->apply_tag( 'newsletter-subscriber' ); // Replace with your desired tag name

        // Mark the email as confirmed in Groundhogg
        if ( class_exists( '\Groundhogg\Preferences' ) ) {
            do_action(
                'groundhogg/step/email/confirmed',
                $contact->get_id(),
                \Groundhogg\Preferences::CONFIRMED,
                \Groundhogg\Preferences::CONFIRMED,
                0 // funnel_id (if you have one, replace 0 with actual ID)
            );
        }
    }

    // If you'd like to do additional actions for terms_agreement:
    // if ( $terms_agree === 'accepted' ) {
    //     // e.g. apply a "Agreed to Terms" tag if you want
    //     // $contact->apply_tag( 'agreed-to-terms' );
    // }
}
