<?php
/**
 * Import content from Drupal database
 * 	drupalport
 * 		posts:
 * 			import the posts, skipping existing posts by default (force an update with --force)
 * 			import all categories (does not duplicate)
 * 			import all campuses (does not duplicate)
 * 			associate categories, campuses, authors (only imported if they're attached to a post), group
 * 			import all related meta
 * 			import any images & replace img tag in content
 * 		pages:
 * 			import the pages, skipping existing pages by default (force an update with --force)
 * 			import all categories (does not duplicate)
 * 			associate categories, authors (only imported if they're attached to a page)
 * 			import all related meta
 * 			import any images & replace img tag in content
 * 		people:
 * 			import the people, skipping existing people by default (force an update with --force)
 * 			import all campuses (does not duplicate)
 * 			associate groups, campuses
 * 			import all related meta
 * 			import any images & replace img tag in content
 * 		projects:
 * 			import the projects, skipping existing projects by default (force an update with --force)
 * 			import all categories (does not duplicate)
 * 			import all campuses (does not duplicate)
 * 			associate categories, campuses, authors (only imported if they're attached to a project), group
 * 			import all related meta
 * 			import any images & replace img tag in content
 *
 *		Useful mysql commands:  UPDATE `wp_posts` SET menu_order = 10 WHERE post_type = 'ctrs-people' AND menu_order = 0;
 *								SELECT * FROM `wp_posts` WHERE post_type = 'ctrs-people' and menu_order = 0;
 */
class CTRS_Drupal_Import extends WP_CLI_Command {
	private $config, $db;
	private $categories = array();
	private $campuses = array();
	private $technologies = array();
	private $authors = array();

	/**
	 * Leaving this to test that wp-cli is working
	 */
	public function hello( $args = array(), $assoc_args = array() ) {
		WP_CLI::success( 'Hello World' );
	}

	/**
	 * Import posts. Attach authors. Do metadata.
	 * Leave Drupal ID as post meta.
	 * Attach categories, tags, authors during import.
	 * Need to be an administrator to grab <object>s for audio
	 *
	 * @synopsis [--force]
	 */
	public function posts( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		$this->db = $this->setup( $args, $assoc_args );
		$force    = isset( $assoc_args['force'] );

		// Import all categories & save Drupal/WP ID mapping
		$this->categories = $this->categories();

		// Import all campuses & save Drupal/WP ID mapping
		$this->campuses = $this->campuses();

		// Import all technologies & save Drupal/WP ID mapping
		$this->technologies = $this->technologies();

		$total = $this->get_total( 'posts' );
		$notify = new \cli\progress\Bar( "Importing $total posts", $total );

		$sql = "SELECT DISTINCT
				n.nid, n.uid, n.vid, FROM_UNIXTIME(n.created) as created, r.body, n.title, r.teaser, IF(SUBSTR(a.dst, 11, 1) = '/', SUBSTR(a.dst, 12), a.dst) as dst, FROM_UNIXTIME(n.changed) as changed, n.type, IF(n.status = 1, 'publish', 'private') as status, f.field_detail_text_value, e.field_excerpt_value
				FROM node n
				INNER JOIN node_revisions r
				USING(vid)
				INNER JOIN content_field_detail_text f
				USING(vid)
				INNER JOIN content_field_excerpt e
				USING(vid)
				LEFT OUTER JOIN url_alias a
				ON a.src = CONCAT('node/', n.nid)
				WHERE n.type IN ('citris_blog_post')
				LIMIT $total;";

		$stmt = $this->db->prepare( $sql );
		$stmt->execute();

		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			// grab the existing post ID (if it exists).
			$wp_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = 'drupal_import_id' AND meta_value = ".$row['nid'] );

			// If we're not forcing import, skip existing posts.
			if ( ! $force && $wp_id )
				continue;

			//$user_id = $this->get_user( $row['uid'] );

			$post = array(
				'post_type'     => 'post',
				'post_status'   => trim( $row['status'] ),
				'post_author'   => 4,
				//'post_name'   => $row['dst'],
				'post_title'    => trim( $row['title'] ),
				'post_content'  => trim( $row['field_detail_text_value'] ),
				'post_date'     => $row['created'],
				'post_modified' => $row['changed'],
				'post_excerpt'  => trim( $row['field_excerpt_value'] ),
			);

			if ( $wp_id ) {
				$post['ID'] = $wp_id;
			}

			$wp_id = wp_insert_post( $post );

			// Download images found in post_content and update post_content with new images.
			$updated_post = array( 'ID' => $wp_id );
			$updated_post['post_content'] = $this->import_media( $row['field_detail_text_value'], $wp_id );
			wp_update_post( $updated_post );

			update_post_meta( $wp_id, 'drupal_import_id', trim( $row['nid'] ) );

			// Featured Image
			$sql = "SELECT filepath FROM files WHERE nid = ?;";
			$image_file_stmt = $this->db->prepare( $sql );
			$image_file_stmt->execute( array( $row['nid'] ) );
			$image = $image_file_stmt->fetch( PDO::FETCH_ASSOC );

			if ( $image && isset( $image['filepath'] ) ) {
				$image = $this->import_featured_image( $image['filepath'], $wp_id );

				if ( ! $image ) {
					WP_CLI::line( "Error: Featured image not added!" );
				}
			} else {
				WP_CLI::line( "Error: No Featured Image Found!" );
			}

			// Categories
			$sql = "SELECT tid FROM term_node WHERE nid = ?;";

			$term_stmt = $this->db->prepare( $sql );
			$term_stmt->execute( array( $row['nid'] ) );
			$terms = $term_stmt->fetchAll();
			$terms = wp_list_pluck( $terms, 'tid' );

			array_walk( $terms, array( $this, '_drupal_to_wp_cat' ) );
			wp_set_post_terms( $wp_id, $terms, 'category' );

			// Technologies
			$sql = "SELECT field_research_technologies_nid FROM content_field_research_technologies WHERE vid = ?;";

			$tech_stmt = $this->db->prepare( $sql );
			$tech_stmt->execute( array( $row['vid'] ) );
			$techs = $tech_stmt->fetchAll();
			$techs = wp_list_pluck( $techs, 'field_research_technologies_nid' );

			array_walk( $techs, array( $this, '_drupal_to_wp_tech' ) );
			wp_set_post_terms( $wp_id, $techs, 'ctrs-technologies' );

			// Groups
			$sql = "SELECT field_research_themes_nid FROM content_field_research_themes WHERE vid = ?;";
			$group_stmt = $this->db->prepare( $sql );
			$group_stmt->execute( array( $row['vid'] ) );
			$groups = $group_stmt->fetchAll();

			if ( $groups ) {
				$groups = wp_list_pluck( $groups, 'field_research_themes_nid' );
				foreach ( $groups as $group ) {
					$sql = "SELECT title FROM node WHERE nid = ?;";
					$group_stmt = $this->db->prepare( $sql );
					$group_stmt->execute( array( $group ) );
					$group = $group_stmt->fetch( PDO::FETCH_ASSOC );

					if ( $group && isset( $group['title'] ) ) {
						$group = $this->set_group( $group['title'], $wp_id );

						if ( ! $group ) {
							WP_CLI::line( "Error: Group not added!" );
						}
					}
				}
			}

			// Campuses
			$sql = "SELECT field_campuses_nid FROM content_field_campuses WHERE vid = ?;";

			$term_stmt = $this->db->prepare( $sql );
			$term_stmt->execute( array( $row['vid'] ) );
			$terms = $term_stmt->fetchAll();
			$terms = wp_list_pluck( $terms, 'field_campuses_nid' );

			array_walk( $terms, array( $this, '_drupal_to_wp_campus' ) );
			wp_set_post_terms( $wp_id, $terms, 'ctrs-campus' );

			$notify->tick();
		}

		$notify->finish();

		WP_CLI::success( "Posts imported." );
	}

	/**
	 * Import pages. Attach authors. Do metadata.
	 * Leave Drupal ID as post meta.
	 * Attach categories, tags, authors during import.
	 * Need to be an administrator to grab <object>s for audio
	 *
	 * @synopsis [--force]
	 */
	public function pages( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		$this->db = $this->setup( $args, $assoc_args );
		$force    = isset( $assoc_args['force'] );

		// Import all categories & save Drupal/WP ID mapping
		$this->categories = $this->categories();

		$total = $this->get_total( 'pages' );
		$notify = new \cli\progress\Bar( "Importing $total pages", $total );

		$sql = "SELECT DISTINCT
				n.nid, n.uid, n.vid, FROM_UNIXTIME(n.created) as created, r.body, n.title, r.teaser, IF(SUBSTR(a.dst, 11, 1) = '/', SUBSTR(a.dst, 12), a.dst) as dst, FROM_UNIXTIME(n.changed) as changed, n.type, IF(n.status = 1, 'publish', 'private') as status
				FROM node n
				INNER JOIN node_revisions r
				USING(vid)
				LEFT OUTER JOIN url_alias a
				ON a.src = CONCAT('node/', n.nid)
				WHERE n.type IN ('page')
				LIMIT $total;";

		$stmt = $this->db->prepare( $sql );
		$stmt->execute();

		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			// grab the existing post ID (if it exists).
			$wp_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = 'drupal_import_id' AND meta_value = ".$row['nid'] );

			// If we're not forcing import, skip existing posts.
			if ( ! $force && $wp_id ) {
				continue;
			}

			$user_id = $this->get_user( $row['uid'] );

			$post = array(
				'post_type'     => 'page',
				'post_status'   => trim( $row['status'] ),
				'post_author'   => $user_id,
				//'post_name'   => $row['dst'],
				'post_title'    => trim( $row['title'] ),
				'post_content'  => trim( $row['body'] ),
				'post_date'     => $row['created'],
				'post_modified' => $row['changed'],
				'post_excerpt'  => trim( $row['teaser'] ),
			);

			if ( $wp_id ) {
				$post['ID'] = $wp_id;
			}

			$wp_id = wp_insert_post( $post );

			// Download images found in post_content and update post_content with new images.
			$updated_post = array( 'ID' => $wp_id );
			$updated_post['post_content'] = $this->import_media( $row['body'], $wp_id );
			wp_update_post( $updated_post );

			update_post_meta( $wp_id, 'drupal_import_id', trim( $row['nid'] ) );

			// Featured Image
			$sql = "SELECT filepath FROM files WHERE nid = ?;";
			$image_file_stmt = $this->db->prepare( $sql );
			$image_file_stmt->execute( array( $row['nid'] ) );
			$image = $image_file_stmt->fetch( PDO::FETCH_ASSOC );

			if ( $image && isset( $image['filepath'] ) ) {
				$image = $this->import_featured_image( $image['filepath'], $wp_id );

				if ( ! $image ) {
					WP_CLI::line( "Error: Featured image not added!" );
				}
			} else {
				WP_CLI::line( "Error: No Featured Image Found!" );
			}

			// Categories.
			$sql = "SELECT tid FROM term_node WHERE nid = ?;";

			$term_stmt = $this->db->prepare( $sql );
			$term_stmt->execute( array( $row['nid'] ) );
			$terms = $term_stmt->fetchAll();
			$terms = wp_list_pluck( $terms, 'tid' );

			array_walk( $terms, array( $this, '_drupal_to_wp_cat' ) );
			wp_set_post_terms( $wp_id, $terms, 'category' );

			$notify->tick();
		}

		$notify->finish();

		WP_CLI::success( "Pages imported." );
	}

	/**
	 * Remove spaces from people names.
	 *
	 */
	public function remove_spaces( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		$this->db  = $this->setup( $args, $assoc_args );
		$post_type = 'ctrs-people';

		$people = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title
				FROM $wpdb->posts
				WHERE post_type = %s
				",
				$post_type
			)
		);
		$total = count( $people );
		$notify = new \cli\progress\Bar( "Importing $total people", $total );

		if ( $people ) {
			foreach ( $people as $person ) {
				$title = $person->post_title;
				$fixed_title = preg_replace( '/\s+/', ' ', $title );

				if ( $title !== $fixed_title ) {
					$update = $wpdb->update(
						$wpdb->posts, // table to update
						array( 'post_title' => $fixed_title ), // column to update and content to update it with
						array( 'ID' => $person->ID ), // Where statement
						array( '%s' ), // Title should be a string
						array( '%d' ) // ID should be a int
					);

					// If a row was updated, clean the cache and output a message.
					if ( $update ) {
						clean_post_cache( $person->ID );
						WP_CLI::line( 'updated: Person ' . absint( $person->ID ) );
					} else {
						WP_CLI::line( 'NOT UPDATED: Person ' . absint( $person->ID ) );
					}
				}

				$notify->tick();
			}
		}

		$notify->finish();

		WP_CLI::success( "People names fixed." );
	}

	/**
	 * Change people type meta
	 *
	 * @synopsis [--type]
	 */
	public function convert_type( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		if ( ! isset( $assoc_args['type'] ) ) {
			WP_CLI::error( 'Please enter a type' );
		}

		$this->db  = $this->setup( $args, $assoc_args );
		$post_type = 'ctrs-people';
		$person_type = strtolower( $assoc_args['type'] );

		$people = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT wp_posts.ID
				FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta ON (wp_posts.ID = wp_postmeta.post_id)
				WHERE post_type = %s
				AND ((wp_postmeta.meta_key = '_ctrs_type' AND CAST(wp_postmeta.meta_value AS CHAR) = %s))
				",
				$post_type,
				$person_type
			)
		);
		$total = count( $people );
		$notify = new \cli\progress\Bar( "Changing $total $person_type people", $total );

		if ( $people ) {
			foreach ( $people as $person ) {
				update_post_meta( $person->ID, '_ctrs_'.$person_type, sanitize_text_field( $person_type ) );

				$notify->tick();
			}
		}

		$notify->finish();

		WP_CLI::success( "People types fixed." );
	}

	/**
	 * Import people. Attach authors. Do metadata.
	 * Leave Drupal ID as post meta.
	 * Attach categories, tags, authors during import.
	 * Need to be an administrator to grab <object>s for audio
	 *
	 * @synopsis [--force]
	 */
	public function people( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		$this->db = $this->setup( $args, $assoc_args );
		$force    = isset( $assoc_args['force'] );

		// Import all technologies & save Drupal/WP ID mapping
		$this->technologies = $this->technologies();

		// Import all campuses & save Drupal/WP ID mapping
		$this->campuses = $this->campuses();

		$total = $this->get_total( 'people' );
		$notify = new \cli\progress\Bar( "Importing $total people", $total );

		$sql = "SELECT DISTINCT
				n.nid, n.uid, n.vid, FROM_UNIXTIME(n.created) as created, r.body, n.title, r.teaser, IF(SUBSTR(a.dst, 11, 1) = '/', SUBSTR(a.dst, 12), a.dst) as dst, FROM_UNIXTIME(n.changed) as changed, n.type, IF(n.status = 1, 'publish', 'private') as status, ft.field_person_type_value, fa.field_address_value
				FROM node n
				INNER JOIN content_field_person_type ft
				USING(vid)
				INNER JOIN content_field_address fa
				USING(vid)
				INNER JOIN node_revisions r
				USING(vid)
				LEFT OUTER JOIN url_alias a
				ON a.src = CONCAT('node/', n.nid)
				WHERE n.type IN ('citris_person')
				LIMIT $total;";

		$stmt = $this->db->prepare( $sql );
		$stmt->execute();

		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			// grab the existing post ID (if it exists).
			$wp_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = 'drupal_import_id' AND meta_value = ".$row['nid'] );

			// If we're not forcing import, skip existing posts.
			if ( ! $force && $wp_id ) {
				continue;
			}

			//$user_id = $this->get_user( $row['uid'] );
			$post_title = preg_replace( '/\s+/', ' ', $row['title'] );
			$post = array(
				'post_type'     => 'ctrs-people',
				'post_status'   => trim( $row['status'] ),
				'post_author'   => 6,
				//'post_name'     => $row['dst'],
				'post_title'    => trim( $post_title ),
				'post_content'  => trim( $row['body'] ),
				'post_date'     => $row['created'],
				'post_modified' => $row['changed'],
				'post_excerpt'  => trim( $row['teaser'] ),
				'menu_order'    => 30
			);

			if ( $wp_id ) {
				$post['ID'] = $wp_id;
			}

			$wp_id = wp_insert_post( $post );

			// Download images found in post_content and update post_content with new images.
			$updated_post = array( 'ID' => $wp_id );
			$updated_post['post_content'] = $this->import_media( $row['body'], $wp_id );
			wp_update_post( $updated_post );

			// Meta
			// Phone, Position, Department
			$sql = "SELECT field_phone_value, field_position_value, field_department_value FROM content_type_citris_person WHERE vid = ?";
			$meta_stmt = $this->db->prepare( $sql );
			$meta_stmt->execute( array( $row['vid'] ) );

			$values = $meta_stmt->fetchAll();
			foreach ( $values as $value ) {
				update_post_meta( $wp_id, '_ctrs_phone', sanitize_text_field( $value['field_phone_value'] ) );
				update_post_meta( $wp_id, '_ctrs_position', sanitize_text_field( $value['field_position_value'] ) );
				update_post_meta( $wp_id, '_ctrs_department', sanitize_text_field( $value['field_department_value'] ) );
			}

			// First name, last name, honorific, image, url, email
			$sql = "SELECT first_name, last_name, honorific, thumbnail, home_url, email FROM citris_person WHERE nid = ?";
			$meta_stmt = $this->db->prepare( $sql );
			$meta_stmt->execute( array( $row['nid'] ) );

			$values = $meta_stmt->fetchAll();
			foreach ( $values as $value ) {
				update_post_meta( $wp_id, '_ctrs_fname', sanitize_text_field( $value['first_name'] ) );
				update_post_meta( $wp_id, '_ctrs_lname', sanitize_text_field( $value['last_name'] ) );
				update_post_meta( $wp_id, '_ctrs_honorific', sanitize_text_field( $value['honorific'] ) );
				update_post_meta( $wp_id, '_ctrs_url', esc_url_raw( $value['home_url'] ) );
				update_post_meta( $wp_id, '_ctrs_email', sanitize_text_field( $value['email'] ) );
			}

			// import id, type, address
			update_post_meta( $wp_id, 'drupal_import_id', $row['nid'] );
			if ( isset( $row['field_person_type_value'] ) && '' !== trim( $row['field_person_type_value'] ) ) {
				if ( 'leadership' === trim( $row['field_person_type_value'] ) ) {
					$type_key = '_ctrs_leadership';
				} elseif ( 'researcher' === trim( $row['field_person_type_value'] ) ) {
					$type_key = '_ctrs_researcher';
				} elseif ( 'staff' === trim( $row['field_person_type_value'] ) ) {
					$type_key = '_ctrs_staff';
				} else {
					$type_key = '_ctrs_type';
				}
				update_post_meta( $wp_id, $type_key, sanitize_text_field( $row['field_person_type_value'] ) );
			}
			if ( isset( $row['field_address_value'] ) && '' !== trim( $row['field_address_value'] ) ) {
				update_post_meta( $wp_id, '_ctrs_address', wp_kses_post( $row['field_address_value'] ) );
			}

			// Technologies
			$sql = "SELECT field_research_technologies_nid FROM content_field_research_technologies WHERE vid = ?;";

			$tech_stmt = $this->db->prepare( $sql );
			$tech_stmt->execute( array( $row['vid'] ) );
			$techs = $tech_stmt->fetchAll();
			$techs = wp_list_pluck( $techs, 'field_research_technologies_nid' );

			array_walk( $techs, array( $this, '_drupal_to_wp_tech' ) );
			wp_set_post_terms( $wp_id, $techs, 'ctrs-technologies' );

			// Groups
			$sql = "SELECT field_research_themes_nid FROM content_field_research_themes WHERE vid = ?;";
			$group_stmt = $this->db->prepare( $sql );
			$group_stmt->execute( array( $row['vid'] ) );
			$groups = $group_stmt->fetchAll();

			if ( $groups ) {
				$groups = wp_list_pluck( $groups, 'field_research_themes_nid' );
				foreach ( $groups as $group ) {
					$sql = "SELECT title FROM node WHERE nid = ?;";
					$group_stmt = $this->db->prepare( $sql );
					$group_stmt->execute( array( $group ) );
					$group = $group_stmt->fetch( PDO::FETCH_ASSOC );

					if ( $group && isset( $group['title'] ) ) {
						$group = $this->set_group( $group['title'], $wp_id );

						if ( ! $group ) {
							WP_CLI::line( "Error: Group not added!" );
						}
					}
				}
			}

			// Campuses
			$sql = "SELECT field_campuses_nid FROM content_field_campuses WHERE vid = ?;";

			$term_stmt = $this->db->prepare( $sql );
			$term_stmt->execute( array( $row['vid'] ) );
			$terms = $term_stmt->fetchAll();
			$terms = wp_list_pluck( $terms, 'field_campuses_nid' );

			array_walk( $terms, array( $this, '_drupal_to_wp_campus' ) );
			wp_set_post_terms( $wp_id, $terms, 'ctrs-campus' );

			// Featured Image
			$sql = "SELECT iid FROM image_attach WHERE nid = ?;";
			$image_id_stmt = $this->db->prepare( $sql );
			$image_id_stmt->execute( array( $row['nid'] ) );
			$image = $image_id_stmt->fetch( PDO::FETCH_ASSOC );

			if ( isset( $image['iid'] ) ) {
				$sql = "SELECT filepath FROM files WHERE nid = ? AND filename = '_original';";
				$image_file_stmt = $this->db->prepare( $sql );
				$image_file_stmt->execute( array( $image['iid'] ) );
				$image = $image_file_stmt->fetch( PDO::FETCH_ASSOC );

				$image = $this->import_featured_image( $image['filepath'], $wp_id );

				if ( ! $image ) {
					WP_CLI::line( "Error: Featured image not added!" );
				}
			} else {
				WP_CLI::line( "Error: No Image ID Found!" );
			}

			$notify->tick();
		}

		$notify->finish();

		WP_CLI::success( "People imported." );
	}

	/**
	 * Import projects. Attach authors. Do metadata.
	 * Leave Drupal ID as post meta.
	 * Attach categories, tags, authors during import.
	 * Need to be an administrator to grab <object>s for audio
	 *
	 * @synopsis [--force]
	 */
	public function projects( $args = array(), $assoc_args = array() ) {
		global $wpdb;

		$this->db = $this->setup( $args, $assoc_args );
		$force    = isset( $assoc_args['force'] );

		// Import all categories & save Drupal/WP ID mapping
		$this->categories = $this->categories();

		// Import all technologies & save Drupal/WP ID mapping
		$this->technologies = $this->technologies();

		// Import all campuses & save Drupal/WP ID mapping
		$this->campuses = $this->campuses();

		$total = $this->get_total( 'projects' );
		$notify = new \cli\progress\Bar( "Importing $total projects", $total );

		$sql = "SELECT DISTINCT
				n.nid, n.uid, n.vid, FROM_UNIXTIME(n.created) as created, n.title, IF(SUBSTR(a.dst, 11, 1) = '/', SUBSTR(a.dst, 12), a.dst) as dst, FROM_UNIXTIME(n.changed) as changed, n.type, IF(n.status = 1, 'publish', 'private') as status, f.field_synopsis_value, e.field_excerpt_value
				FROM node n
				INNER JOIN content_field_synopsis f
				USING(vid)
				INNER JOIN content_field_excerpt e
				USING(vid)
				LEFT OUTER JOIN url_alias a
				ON a.src = CONCAT('node/', n.nid)
				WHERE n.type IN ('research_project')
				LIMIT $total;";

		$stmt = $this->db->prepare( $sql );
		$stmt->execute();

		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			// grab the existing post ID (if it exists).
			$wp_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = 'drupal_import_id' AND meta_value = ".$row['nid'] );

			// If we're not forcing import, skip existing posts.
			if ( ! $force && $wp_id ) {
				continue;
			}

			$user_id = $this->get_user( $row['uid'] );

			$post = array(
				'post_type'     => 'ctrs-projects',
				'post_status'   => trim( $row['status'] ),
				'post_author'   => $user_id,
				//'post_name'   => $row['dst'],
				'post_title'    => trim( $row['title'] ),
				'post_content'  => trim( $row['field_synopsis_value'] ),
				'post_date'     => $row['created'],
				'post_modified' => $row['changed'],
				'post_excerpt'  => trim( $row['field_excerpt_value'] ),
			);

			if ( $wp_id ) {
				$post['ID'] = $wp_id;
			}

			$wp_id = wp_insert_post( $post );

			// Download images found in post_content and update post_content with new images.
			$updated_post = array( 'ID' => $wp_id );
			$updated_post['post_content'] = $this->import_media( $row['field_synopsis_value'], $wp_id );
			wp_update_post( $updated_post );

			update_post_meta( $wp_id, 'drupal_import_id', trim( $row['nid'] ) );

			// Featured Image
			$sql = "SELECT filepath FROM files WHERE nid = ?;";
			$image_file_stmt = $this->db->prepare( $sql );
			$image_file_stmt->execute( array( $row['nid'] ) );
			$image = $image_file_stmt->fetch( PDO::FETCH_ASSOC );

			if ( $image && isset( $image['filepath'] ) ) {
				$image = $this->import_featured_image( $image['filepath'], $wp_id );

				if ( ! $image ) {
					WP_CLI::line( "Error: Featured image not added!" );
				}
			} else {
				WP_CLI::line( "Error: No Featured Image Found!" );
			}

			// Categories.
			$sql = "SELECT tid FROM term_node WHERE nid = ?;";

			$term_stmt = $this->db->prepare( $sql );
			$term_stmt->execute( array( $row['nid'] ) );
			$terms = $term_stmt->fetchAll();
			$terms = wp_list_pluck( $terms, 'tid' );

			array_walk( $terms, array( $this, '_drupal_to_wp_cat' ) );
			wp_set_post_terms( $wp_id, $terms, 'category' );

			// Technologies
			$sql = "SELECT field_research_technologies_nid FROM content_field_research_technologies WHERE vid = ?;";

			$tech_stmt = $this->db->prepare( $sql );
			$tech_stmt->execute( array( $row['vid'] ) );
			$techs = $tech_stmt->fetchAll();
			$techs = wp_list_pluck( $techs, 'field_research_technologies_nid' );

			array_walk( $techs, array( $this, '_drupal_to_wp_tech' ) );
			wp_set_post_terms( $wp_id, $techs, 'ctrs-technologies' );

			// Groups
			$sql = "SELECT field_research_themes_nid FROM content_field_research_themes WHERE vid = ?;";
			$group_stmt = $this->db->prepare( $sql );
			$group_stmt->execute( array( $row['vid'] ) );
			$groups = $group_stmt->fetchAll();

			if ( $groups ) {
				$groups = wp_list_pluck( $groups, 'field_research_themes_nid' );
				foreach ( $groups as $group ) {
					$sql = "SELECT title FROM node WHERE nid = ?;";
					$group_stmt = $this->db->prepare( $sql );
					$group_stmt->execute( array( $group ) );
					$group = $group_stmt->fetch( PDO::FETCH_ASSOC );

					if ( $group && isset( $group['title'] ) ) {
						$group = $this->set_group( $group['title'], $wp_id );

						if ( ! $group ) {
							WP_CLI::line( "Error: Group not added!" );
						}
					}
				}
			}

			// People and Campuses
			$sql = "SELECT field_researchers_nid FROM content_field_researchers WHERE vid = ?;";
			$people_stmt = $this->db->prepare( $sql );
			$people_stmt->execute( array( $row['vid'] ) );
			$people = $people_stmt->fetchAll();

			$people = wp_list_pluck( $people, 'field_researchers_nid' );
			$people_names = array();
			$campus = array();

			foreach ( $people as $person ) {
				$person_id = $wpdb->get_var( $sql = "SELECT post_id from {$wpdb->postmeta} WHERE meta_key = 'drupal_import_id' AND meta_value = ".$person );
				$name = get_the_title( (int) $person_id );
				$people_names[] = $name;

				$post = get_page_by_title( $name, OBJECT, 'ctrs-people' );
				if ( $post && null !== $post ) {
					$campus_terms = wp_get_object_terms( $post->ID, 'ctrs-campus', array( 'fields' => 'names' ) );

					if ( ! empty( $campus_terms ) && ! is_wp_error( $campus_terms ) ) {
						$campus = array_merge( $campus, $campus_terms );
					}
				}
			}

			if ( ! empty( $people_names ) ) {
				wp_set_object_terms( $wp_id, array_unique( $people_names ), 'ctrs-tax-people' );
			}

			if ( ! empty( $campus ) ) {
				wp_set_object_terms( $wp_id, array_unique( $campus ), 'ctrs-campus' );
			}

			$notify->tick();
		}

		$notify->finish();

		WP_CLI::success( "Projects imported." );
	}

	/**
	 * Import all categories;
	 * @return array  map of category IDs; Drupal ID => WP ID
	 */
	private function categories() {
		$wp_cats = array();

		$sql = "SELECT d.tid, d.name, d.description FROM term_data d INNER JOIN term_hierarchy h USING(tid) WHERE parent = 0;";
		$stmt = $this->db->prepare( $sql );
		$stmt->execute();
		$parents = $stmt->fetchAll( PDO::FETCH_ASSOC );

		foreach ( $parents as $parent ) {
			$wp_cats[ $parent['tid'] ] = $this->_make_cat( $parent['name'], $parent['description'] );

			$sql = "SELECT d.tid, d.name, d.description FROM term_data d INNER JOIN term_hierarchy h USING(tid) WHERE parent = ". $parent['tid'];
			$stmt = $this->db->prepare( $sql );
			$stmt->execute();

			while ( $row = $stmt->fetch() ) {
				$wp_cats[ $row['tid'] ] = $this->_make_cat( $row['name'], $row['description'], $wp_cats[ $parent['tid'] ] );
			}
		}

		return $wp_cats;
	}

	/**
	 * Set Group term, or import if doesn't exist.
	 *
	 * @return bool
	 */
	private function set_group( $name, $post_id ) {
		$name = htmlentities( $name );
		$term = '';
		$term_name = '';

		switch ( $name ) {
			case 'i4Energy: Information Technology, Sensors, and Controls for Stable and Sustainable Energy':
				$term_name = 'Energy';
				break;
			case 'Delivering &ldquo;Quality Health Care Everywhere&rdquo; for Californians':
				$term_name = 'Health Care';
				break;
			case 'Intelligent Infrastructure: Water, Transport, Cities':
				$term_name = 'Intelligent Infrastructure';
				break;
			case 'Data and Democracy':
				$term_name = 'democracy';
				break;
			case 'Generating Insight from &ldquo;Big Data&rdquo;':
				$term_name = 'Big Data';
				break;
		}

		if ( $term = term_exists( $term_name, 'ctrs-groups' ) ) {
			wp_set_object_terms( $post_id, (int) $term['term_id'], 'ctrs-groups' );

			return $term['term_id'];
		} else {
			$term = wp_insert_term( $term_name, 'ctrs-groups' );

			if ( is_wp_error( $term ) ) {
				WP_CLI::line( "Error: Group $name not imported." );
				return -1;
			} else {
				wp_set_object_terms( $post_id, (int) $term['term_id'], 'ctrs-groups' );

				return $term['term_id'];
			}
		}
	}

	/**
	 * Import all campus's.
	 *
	 * @return array  map of campus IDs; Drupal ID => WP ID
	 */
	private function campuses() {
		$campuses = array();

		$total = $this->get_total( 'campus' );
		$sql   = "SELECT DISTINCT
				n.nid, n.uid, FROM_UNIXTIME(n.created) as created, n.title, n.type, f.field_detail_text_value, e.field_intro_text_value
				FROM node n
				INNER JOIN node_revisions r
				USING(vid)
				INNER JOIN content_field_detail_text f
				USING(vid)
				INNER JOIN content_field_intro_text e
				USING(vid)
				WHERE n.type IN ('campus')
				LIMIT $total;";
		$stmt = $this->db->prepare( $sql );
		$stmt->execute();

		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$desc = $row['field_intro_text_value'] . $row['field_detail_text_value'];
			$campuses[ $row['nid'] ] = $this->_make_campus( $row['title'], $desc );
		}

		return $campuses;
	}

	/**
	 * Import all technologies.
	 *
	 * @return array  map of tech IDs; Drupal ID => WP ID
	 */
	private function technologies() {
		$technologies = array();

		$total = $this->get_total( 'research_technology' );
		$sql   = "SELECT DISTINCT
				n.nid, n.title, r.body
				FROM node n
				INNER JOIN node_revisions r
				USING(vid)
				WHERE n.type IN ('research_technology')
				LIMIT $total;";
		$stmt = $this->db->prepare( $sql );
		$stmt->execute();

		while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
			$desc = $row['body'];
			$technologies[ $row['nid'] ] = $this->_make_tech( $row['title'], $desc );
		}

		return $technologies;
	}

	/**
	 * Download all images found in post_content and update those image paths.
	 */
	private function import_media( $content, $post_id = 0 ) {
		preg_match_all( '#<img(.*?)src="(.*?)"(.*?)>#', $content, $matches, PREG_SET_ORDER );

		if ( is_array( $matches ) ) {
			foreach ( $matches as $match ) {
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				$old_filename = '';
				$filename = $match[2];
				$img = $match[0];
				$filename = urldecode( $filename ); // for filenames with spaces

				if ( preg_match( '/^http/', $filename ) ) {
					$old_filename = $filename;
				} elseif ( '/files/' === substr( $filename, 0, 7 ) || '/sites/' === substr( $filename, 0, 7 ) ) {
					$old_filename = 'http://citris-uc.org/' . $filename;
				}

				$tmp = download_url( $old_filename );
				preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $filename, $matches );
				$file_array['name'] = basename( $matches[0] );
				$file_array['tmp_name'] = $tmp;

				// If error storing temporarily, unlink
				if ( is_wp_error( $tmp ) ) {
					@unlink( $file_array['tmp_name'] );
					$file_array['tmp_name'] = '';
				}

				// do the validation and storage stuff
				$id = media_handle_sideload( $file_array, $post_id );

				// If error storing permanently, unlink
				if ( is_wp_error( $id ) ) {
					@unlink($file_array['tmp_name']);
					WP_CLI::line( "Error: ". $id->get_error_message() );
				} else {
					$src = wp_get_attachment_url( $id );

					if ( $src ) {
						$content = str_replace( $filename, $src, $content );
					} else {
						WP_CLI::line( "Error: $old_filename not changed in post content." );
					}
				}
			}
		}

		return $content;
	}

	/**
	 * Download featured image.
	 */
	private function import_featured_image( $filepath, $post_id = 0 ) {
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$featured_image = '';
		$old_filename = '';
		$filename = $filepath;
		$filename = urldecode( $filename ); // for filenames with spaces

		if ( preg_match( '/^http/', $filename ) ) {
			$old_filename = $filename;
		} elseif ( 'files/' === substr( $filename, 0, 6 ) ) {
			$old_filename = 'http://citris-uc.org/' . $filename;
		}

		$tmp = download_url( $old_filename );
		preg_match( '/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $filename, $matches );

		// make sure we have a match.  This won't be set for PDFs and .docs
		if ( $matches && isset( $matches[0] ) ) {
			$file_array['name'] = basename( $matches[0] );
			$file_array['tmp_name'] = $tmp;

			// If error storing temporarily, unlink
			if ( is_wp_error( $tmp ) ) {
				@unlink( $file_array['tmp_name'] );
				$file_array['tmp_name'] = '';
			}

			// do the validation and storage stuff
			$id = media_handle_sideload( $file_array, $post_id );

			// If error storing permanently, unlink
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
				WP_CLI::line( "Error: ". $id->get_error_message() );
			} else {
				$featured_image = set_post_thumbnail( $post_id, $id );
			}
		} else {
			WP_CLI::line( "Error: ". $filename . " not added." );
		}

		return $featured_image;
	}

	/* ===========
	 *  UTILITIES
	 * =========== */

	/**
	 * Set up the PDO object. Defaults are set up for a local DB, you can
	 * override these by passing parameters to function (--user=username --pass=mypass etc)
	 */
	private function setup( $args = array(), $assoc_args = array() ) {
		$defaults = spyc_load_file( dirname( __FILE__ ) . '/wp-cli.local.yml' );
		$this->config = wp_parse_args( $assoc_args, $defaults );
		extract( $this->config['database'], EXTR_SKIP );

		try {
			$db = new PDO( 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database, $db_user, $pass, array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'' ) );

			return $db;

		} catch ( PDOException $e ) {
			WP_CLI::error( 'Could not connect to database: '. $e->getMessage() );
			die();
		}
	}

	/**
	 * Get the total number of posts to import.
	 */
	private function get_total( $type ) {
		$count_sql = "SELECT COUNT(*) as total FROM node";

		if ( 'posts' === $type ) {
			$count_sql .= " WHERE type = 'citris_blog_post'";
		} elseif ( 'campus' === $type ) {
			$count_sql .= " WHERE type = 'campus'";
		} elseif ( 'projects' === $type ) {
			$count_sql .= " WHERE type = 'research_project'";
		} elseif ( 'people' === $type ) {
			$count_sql .= " WHERE type = 'citris_person'";
		} elseif ( 'pages' === $type ) {
			$count_sql .= " WHERE type = 'page'";
		} elseif ( 'research_technology' === $type ) {
			$count_sql .= " WHERE type = 'research_technology'";
		}

		$stmt = $this->db->query( $count_sql );
		$result = $stmt->fetch( PDO::FETCH_ASSOC );

		return $result['total'];
	}

	/**
	 * Make a category
	 */
	private function _make_cat( $name, $desc, $parent = 0 ) {
		if ( $term = term_exists( $name, 'category', $parent ) ) {
			return $term['term_id'];
		}

		$args = array(
			'parent' => $parent,
			'description' => $desc
		);
		$term = wp_insert_term( $name, 'category', $args );

		if ( is_wp_error( $term ) ) {
			WP_CLI::line( "Error: Category $name not imported." );
			return -1;
		}

		return $term['term_id'];
	}

	/**
	 * Make a campus
	 */
	private function _make_campus( $name, $desc, $parent = 0 ) {
		if ( $term = term_exists( $name, 'ctrs-campus', $parent ) ) {
			return $term['term_id'];
		}

		$args = array(
			'parent' => $parent,
			'description' => $desc
		);
		$term = wp_insert_term( $name, 'ctrs-campus', $args );

		if ( is_wp_error( $term ) ) {
			WP_CLI::line( "Error: Campus $name not imported." );
			return -1;
		}

		return $term['term_id'];
	}

	/**
	 * Make a technology
	 */
	private function _make_tech( $name, $desc, $parent = 0 ) {
		if ( $term = term_exists( $name, 'ctrs-technologies', $parent ) ) {
			return $term['term_id'];
		}

		$args = array(
			'parent' => $parent,
			'description' => $desc
		);
		$term = wp_insert_term( $name, 'ctrs-technologies', $args );

		if ( is_wp_error( $term ) ) {
			WP_CLI::line( "Error: Technology $name not imported." );
			return -1;
		}

		return $term['term_id'];
	}

	/**
	 * Convert a Drupal category ID to a WP term_id. Used in array_walk
	 * to convert array of Drupal ids.
	 */
	private function _drupal_to_wp_cat( &$term, $i ) {
		$term = $this->categories[ (int) $term ];
	}

	/**
	 * Convert a Drupal campus ID to a WP term_id. Used in array_walk
	 * to convert array of Drupal ids.
	 */
	private function _drupal_to_wp_campus( &$term, $i ) {
		if ( (int) $term !== 0 ) {
			$term = $this->campuses[ (int) $term ];
		}
	}

	/**
	 * Convert a Drupal tech ID to a WP term_id. Used in array_walk
	 * to convert array of Drupal ids.
	 */
	private function _drupal_to_wp_tech( &$term, $i ) {
		if ( (int) $term !== 0 ) {
			$term = $this->technologies[ (int) $term ];
		}
	}

	/**
	 * Get WP user ID from the Drupal member ID. If the id is -1, default to WP user 1.
	 * Import user if not in WP (given only the member id, grab the user row from the DB).
	 */
	private function get_user( $id ) {
		if ( 0 === $id ) {
			return 1;
		}

		if ( isset( $this->authors[ $id ] ) ) {
			return $this->authors[ $id ];
		}

		$sql = "SELECT uid, name, mail, pass, FROM_UNIXTIME(created) as created FROM users WHERE uid = ?;";

		$stmt = $this->db->prepare( $sql );
		$stmt->execute( array( $id ) );
		$user = $stmt->fetch( PDO::FETCH_ASSOC );

		if ( $user_id = email_exists( $user[ 'mail' ] ) ) {
			$this->authors[ $id ] = $user_id;
			return $user_id;
		}

		$user_id = wp_insert_user( array (
			'user_login'      => $user['name'],
			'user_email'      => $user['mail'],
			'user_registered' => $user['created'],
			'user_pass'       => $user['pass']
		) );

		if ( ! is_wp_error( $user_id ) ) {
			$this->authors[ $id ] = $user_id;
			return $user_id;
		}

		return -1;
	}

}

WP_CLI::add_command( 'drupalport', 'CTRS_Drupal_Import' );