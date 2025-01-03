<?php
declare(strict_types=1);

/**
 * Plugin Name: BuddyBoss + Groundhogg Registration
 * Plugin URI:  https://github.com/clsr-ent/buddyboss-groundhogg-registration
 * Description: Adds newsletter subscription checkbox to BuddyBoss register page and maps to Groundhogg fields
 * Version:     1.1.1
 * Author:      Closrr.com
 * Author URI:  https://closrr.com/
 * Text Domain: groundhogg
 * Requires PHP: 8.0
 * Created:     2025-01-03 22:16:00 UTC
 * Created By:  clsr-ent
 */

if (!defined('ABSPATH')) {
    exit; // No direct access.
}

/**
 * Class BuddyBossGroundhoggRegistration
 * 
 * Handles the integration between BuddyBoss registration and Groundhogg
 */
class BuddyBossGroundhoggRegistration {
    /**
     * Initialize the plugin
     */
    public function __construct() {
        // Only add the newsletter checkbox, removed terms checkbox as it's handled by BuddyBoss
        add_action('bp_before_registration_submit_buttons', [$this, 'add_newsletter_checkbox'], 5);
        add_action('user_register', [$this, 'handle_user_registration']);
    }

    /**
     * Add newsletter checkbox to registration form
     * 
     * @return void
     */
    public function add_newsletter_checkbox(): void {
        ?>
        <!-- Newsletter Checkbox -->
        <div class="input-options checkbox-options">
            <div class="bp-checkbox-wrap">
                <input type="checkbox"
                       name="signup_newsletter"
                       id="signup_newsletter"
                       value="1"
                       class="bs-styled-checkbox"
                       <?php checked(!empty($_POST['signup_newsletter']), 1); ?> />
                <label for="signup_newsletter" class="option-label">
                    <?php esc_html_e('Subscribe to our platform and product updates.', 'groundhogg'); ?>
                </label>
            </div>
        </div>
        <?php
    }

    /**
     * Handle user registration
     * 
     * @param int $user_id
     * @return void
     */
    public function handle_user_registration(int $user_id): void {
        if (!$this->verify_groundhogg()) {
            return;
        }

        $wp_user = get_userdata($user_id);
        if (!$wp_user) {
            return;
        }

        $contact = $this->create_or_update_contact($wp_user);
        if (!$contact) {
            return;
        }

        $this->process_terms_agreement($contact);
        $this->process_newsletter_subscription($contact);
    }

    /**
     * Verify Groundhogg is active and available
     * 
     * @return bool
     */
    private function verify_groundhogg(): bool {
        return function_exists('generate_contact_with_map');
    }

    /**
     * Create or update contact in Groundhogg
     * 
     * @param WP_User $wp_user
     * @return mixed
     */
    private function create_or_update_contact($wp_user) {
        $data = [
            'fname' => $wp_user->first_name,
            'lname' => $wp_user->last_name,
            'email_address' => $wp_user->user_email,
            'gdpr_consent' => !empty($_POST['signup_newsletter']) ? 'accepted' : '',
            'terms_agreement' => !empty($_POST['register-privacy-policy']) ? 'accepted' : '',
        ];

        $map = [
            'fname' => 'first_name',
            'lname' => 'last_name',
            'email_address' => 'email',
            'gdpr_consent' => 'meta',
            'terms_agreement' => 'meta',
        ];

        return \Groundhogg\generate_contact_with_map($data, $map);
    }

    /**
     * Process terms agreement for contact
     * 
     * @param mixed $contact
     * @return void
     */
    private function process_terms_agreement($contact): void {
        // Using BuddyBoss's native terms checkbox field
        if (!empty($_POST['register-privacy-policy'])) {
            $contact->set_terms_agreement(\Groundhogg\Contact::Yes);
        }
    }

    /**
     * Process newsletter subscription for contact
     * 
     * @param mixed $contact
     * @return void
     */
    private function process_newsletter_subscription($contact): void {
        if (empty($_POST['signup_newsletter'])) {
            return;
        }

        $contact->apply_tag('newsletter-subscriber');

        if (class_exists('\Groundhogg\Preferences')) {
            do_action(
                'groundhogg/step/email/confirmed',
                $contact->get_id(),
                \Groundhogg\Preferences::CONFIRMED,
                \Groundhogg\Preferences::CONFIRMED,
                0
            );
        }
    }
}

// Initialize the plugin
add_action('plugins_loaded', function() {
    new BuddyBossGroundhoggRegistration();
});
