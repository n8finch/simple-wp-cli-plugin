<?php
/**
 * Plugin Name: IP WP-CLI Commands
 * Version: 0.1
 * Plugin URI: https://n8finch.com/
 * Description: Some rando wp-cli commands to make life easier...
 * Author: Nate Finch
 * Author URI: https://n8finch.com/
 * Text Domain: ip-wpcli
 * Domain Path: /languages/
 * License: GPL v3
 */

 if ( !defined( 'WP_CLI' ) && WP_CLI ) {
	 //Then we don't want to load the plugin
     return;
 }


 /**
  * Implements N8F WP-CLI commands.
  */
class N8F_WP_CLI_COMMANDS extends WP_CLI_Command {

	/**
	 * Remove a PMPro level from a user
	 *
	 * ## OPTIONS
	 * --email=<email>
	 * : Email of user to check against
	 *
	 * [--dry-run]
	 * : Run the entire search/replace operation and show report, but don't save
	 * changes to the database.
	 *
	 * ## EXAMPLES
	 *
	 * wp n8f remove_user --email=me@test.com,another@email.com, and@another.com --dry-run
	 *
	 * @synopsis --email=<email> [--dry-run]
	 *
	 * @when after_wp_load
	 */
	public function remove_user ( $args, $assoc_args ) {

		//Keep a tally of warnings and loops
		$total_warnings = 0;
		$total_users_removed = 0;
		$dry_suffix = '';
		$emails_not_existing = array();

		//Get the args
		$dry_run = $assoc_args['dry-run'];
		$emails = explode( ',', $assoc_args['email'] );

		//loop through emails
		foreach ( $emails as $email ) {

			//Get User ID
			$user_id = email_exists($email);

			if( !$user_id ) {

				WP_CLI::warning( "The user {$email} does not seem to exist." );

				array_push( $emails_not_existing, $email );

				$total_warnings++;

				continue;
			}

			//Check user role
			$the_user = get_user_by('email', $email );
			$is_subscriber = user_can( $the_user->ID, 'subscriber' );
			if ( $is_subscriber ) {

				if ( !$dry_run ) {
					wp_delete_user( $the_user->ID );
				}

				WP_CLI::success( "{$email} deleted as a user" . PHP_EOL );

				$total_users_removed++;

			} else {

				WP_CLI::warning( "The user {$email} is not a subscriber." );

				$total_warnings++;
			}
			
			$total_loops++;
		} //end foreach

		if ( $dry_run ) {

			$dry_suffix = 'BUT, nothing really changed because this was a dry run:-).';

		}

		WP_CLI::success( "{$total_users_removed} User/s been removed, with {$total_warnings} warnings. {$dry_suffix}" );


		if ( $total_warnings ) {

			$emails_not_existing = implode(',', $emails_not_existing);

			WP_CLI::warning(

				"These are the emails to double check and make sure things are on the up and up:" . PHP_EOL .
				"Non-existent emails: " . $emails_not_existing . PHP_EOL
			);

		}
	}
	
}

 WP_CLI::add_command( 'n8f', 'N8F_WP_CLI_COMMANDS' );
