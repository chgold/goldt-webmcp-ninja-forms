<?php
/**
 * Ninja Forms module — exposes forms, fields, actions and submissions as AI tools.
 *
 * @package GoldtWebMCP\NinjaForms
 */

namespace GoldtWebMCP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Forms tools module (`ninjaforms.*`).
 */
class NinjaForms_Module extends Module_Base {

	/**
	 * Module name — the tool-name prefix in the manifest.
	 *
	 * @var string
	 */
	protected $module_name = 'ninjaforms';

	/**
	 * Register this module's tools.
	 *
	 * @return void
	 */
	protected function register_tools() {
		// ── Reads (free) ──
		$this->register_tool(
			'listForms',
			array(
				'description'    => 'List all Ninja Forms (id, title, field count).',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => (object) array(),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getForm',
			array(
				'description'    => 'Get a Ninja Forms form with its settings.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array( 'form_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getFormFields',
			array(
				'description'    => 'List the fields of a form (key, type, label, required).',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array( 'form_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getFormActions',
			array(
				'description'    => 'List the actions (email, store submission, redirect…) configured on a form.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array( 'form_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getSubmissions',
			array(
				'description'    => 'List submissions of a form with their field values.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array(
						'form_id' => array( 'type' => 'integer' ),
						'limit'   => array( 'type' => 'integer' ),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getSubmission',
			array(
				'description'    => 'Get a single submission with its field values.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'submission_id' ),
					'properties' => array( 'submission_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'countSubmissions',
			array(
				'description'    => 'Count submissions of a form.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array( 'form_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);

		// ── Reads added in 1.1.0 (free) ──
		$sub_filters = array(
			'form_id'   => array(
				'type'        => 'integer',
				'description' => 'Restrict to one form (optional).',
			),
			'date_from' => array(
				'type'        => 'string',
				'description' => 'Earliest submission date, YYYY-MM-DD (inclusive).',
			),
			'date_to'   => array(
				'type'        => 'string',
				'description' => 'Latest submission date, YYYY-MM-DD (inclusive).',
			),
			'status'    => array(
				'type'        => 'string',
				'enum'        => array( 'active', 'trash', 'all' ),
				'description' => 'active (default), trash, or all.',
			),
			'user_id'   => array(
				'type'        => 'integer',
				'description' => 'Only submissions made by this logged-in WordPress user.',
			),
			'limit'     => array(
				'type'        => 'integer',
				'description' => 'Page size, 1-200 (default 50).',
			),
			'page'      => array(
				'type'        => 'integer',
				'description' => 'Page number, starting at 1.',
			),
		);
		$this->register_tool(
			'listSubmissions',
			array(
				'description'    => 'List form submissions with filters (form, date range, status, user) and pagination. Returns submission id, form id, sequence number, date, user and field values.',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => array_merge(
						$sub_filters,
						array(
							'include_values' => array(
								'type'        => 'boolean',
								'description' => 'Include field values (default true).',
							),
						)
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'searchSubmissions',
			array(
				'description'    => 'Search form submissions by keyword across all field values, with optional form, date range and status filters. Returns matching submissions and which fields matched.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'query' ),
					'properties' => array_merge(
						array(
							'query' => array(
								'type'        => 'string',
								'description' => 'Keyword to look for (min 2 characters, case-insensitive substring).',
							),
						),
						$sub_filters
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'exportSubmissions',
			array(
				'description'    => 'Export the submissions of a form as CSV text (opens in Excel), with optional date range and field selection. Returns the CSV content inline — no file is written to the server.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array(
						'form_id'    => array( 'type' => 'integer' ),
						'date_from'  => $sub_filters['date_from'],
						'date_to'    => $sub_filters['date_to'],
						'status'     => $sub_filters['status'],
						'field_keys' => array(
							'type'        => 'array',
							'items'       => array( 'type' => 'string' ),
							'description' => 'Field keys to include as columns (default: all fields).',
						),
						'limit'      => array(
							'type'        => 'integer',
							'description' => 'Maximum rows, 1-2000 (default 500).',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getField',
			array(
				'description'    => 'Get the full configuration of one form field (label, key, type, required, placeholder, default, limits, mask, help text, options, calculation settings…).',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array(
						'form_id'        => array( 'type' => 'integer' ),
						'field_id'       => array(
							'type'        => 'integer',
							'description' => 'Field ID (give field_id or field_key).',
						),
						'field_key'      => array(
							'type'        => 'string',
							'description' => 'Field key (give field_id or field_key).',
						),
						'include_styles' => array(
							'type'        => 'boolean',
							'description' => 'Include the *_styles_* display settings (default false).',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'listNotifications',
			array(
				'description'    => 'List the email notifications (email actions) of a form: recipient, subject, message template, from/reply-to/cc/bcc, format, CSV attachment, active flag and conditions.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array( 'form_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getFormEmbedCode',
			array(
				'description'    => 'Get the shortcode, block markup and PHP call that display a form on a page or post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array( 'form_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getCalculations',
			array(
				'description'    => 'Get the calculations (name, equation, decimals) configured on a form, plus the fields that carry calculation values or display calculation results.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array( 'form_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);

		// ── Writes (pro) ──
		$this->register_tool(
			'updateFormTitle',
			array(
				'description'    => 'Rename a Ninja Forms form.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id', 'title' ),
					'properties' => array(
						'form_id' => array( 'type' => 'integer' ),
						'title'   => array( 'type' => 'string' ),
					),
				),
				'required_scope' => 'write',
			)
		);
		$this->register_tool(
			'deleteSubmission',
			array(
				'description'    => 'Delete a form submission.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'submission_id' ),
					'properties' => array( 'submission_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'write',
			)
		);
		$this->register_tool(
			'duplicateForm',
			array(
				'description'    => 'Duplicate an existing form.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array( 'form_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'write',
			)
		);
	}

	/**
	 * Read the optional `limit` parameter (absint), falling back to a default when empty.
	 *
	 * @param array $params   Validated tool input.
	 * @param int   $fallback Limit used when the parameter is missing or 0.
	 * @return int
	 */
	private function limit_param( $params, $fallback ) {
		$limit = \absint( $params['limit'] ?? $fallback );
		return $limit ? $limit : $fallback;
	}

	/**
	 * Deny unless the current user has a capability.
	 *
	 * @param string $cap Capability name.
	 * @return \WP_Error|null
	 */
	private function require_cap( $cap ) {
		if ( ! \current_user_can( $cap ) ) {
			return new \WP_Error( 'forbidden', "{$cap} capability required", array( 'status' => 403 ) );
		}
		return null;
	}

	/**
	 * Permission ceiling for reading submissions (SEC-00).
	 *
	 * Mirrors Ninja Forms' own rule exactly — Submissions route
	 * get_submissions_permission_callback(): `manage_options`, passed through the
	 * `ninja_forms_api_allow_get_submissions` filter (bool $allowed, WP_REST_Request).
	 * A site that widens/narrows submission access for NF's own UI/REST gets the
	 * same result through these tools.
	 *
	 * @return \WP_Error|null
	 */
	private function require_submissions_read() {
		$allowed = \current_user_can( 'manage_options' );
		$request = new \WP_REST_Request( 'GET', '/ninja-forms-submissions/submissions/get' );
		if ( ! \apply_filters( 'ninja_forms_api_allow_get_submissions', $allowed, $request ) ) {
			return new \WP_Error( 'forbidden', 'manage_options capability required (ninja_forms_api_allow_get_submissions)', array( 'status' => 403 ) );
		}
		return null;
	}

	/**
	 * Permission ceiling for reading form definitions (SEC-00).
	 *
	 * Mirrors the Ninja Forms form builder (REST/AJAX controllers such as
	 * includes/AJAX/REST/Forms.php): `manage_options`, filterable through
	 * `ninja_forms_admin_all_forms_capabilities`.
	 *
	 * @return \WP_Error|null
	 */
	private function require_forms_read() {
		$cap = \apply_filters( 'ninja_forms_admin_all_forms_capabilities', 'manage_options' );
		if ( ! \current_user_can( $cap ) ) {
			return new \WP_Error( 'forbidden', "{$cap} capability required (ninja_forms_admin_all_forms_capabilities)", array( 'status' => 403 ) );
		}
		return null;
	}

	/**
	 * Load a form if it exists.
	 *
	 * @param int $form_id Ninja Forms form ID.
	 * @return object|null
	 */
	private function form_exists( $form_id ) {
		if ( ! $form_id ) {
			return null;
		}
		$form = \Ninja_Forms()->form( $form_id )->get();
		// The NF model echoes back any requested ID; a missing form has no settings.
		return ( $form && $form->get_id() && $form->get_settings() ) ? $form : null;
	}

	/**
	 * Map field IDs to key/label/type for a form.
	 *
	 * @param int $form_id Ninja Forms form ID.
	 * @return array
	 */
	private function field_map( $form_id ) {
		$map = array();
		foreach ( \Ninja_Forms()->form( $form_id )->get_fields() as $f ) {
			$map[ (int) $f->get_id() ] = array(
				'key'   => $f->get_setting( 'key' ),
				'label' => $f->get_setting( 'label' ),
				'type'  => $f->get_setting( 'type' ),
			);
		}
		return $map;
	}

	/**
	 * Collect a submission's non-empty field values keyed by field key.
	 *
	 * @param int   $sub_id Submission (nf_sub) post ID.
	 * @param array $fmap   Field map from field_map().
	 * @return array
	 */
	private function submission_values( $sub_id, array $fmap ) {
		$values = array();
		foreach ( $fmap as $fid => $meta ) {
			$v = \get_post_meta( $sub_id, '_field_' . $fid, true );
			if ( '' !== $v ) {
				$values[ ( $meta['key'] ? $meta['key'] : 'field_' . $fid ) ] = $v;
			}
		}
		return $values;
	}

	/**
	 * Tool handler for `ninjaforms.listForms`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_listForms( $params ) {
		$deny = $this->require_forms_read();
		if ( $deny ) {
			return $deny;
		}
		$out = array();
		foreach ( \Ninja_Forms()->form()->get_forms() as $form ) {
			$id    = (int) $form->get_id();
			$out[] = array(
				'id'     => $id,
				'title'  => $form->get_setting( 'title' ),
				'fields' => count( \Ninja_Forms()->form( $id )->get_fields() ),
			);
		}
		return $this->success_response(
			array(
				'forms' => $out,
				'count' => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.getForm`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getForm( $params ) {
		$deny = $this->require_forms_read();
		if ( $deny ) {
			return $deny;
		}
		$id   = \absint( $params['form_id'] ?? 0 );
		$form = $this->form_exists( $id );
		if ( ! $form ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		return $this->success_response(
			array(
				'id'       => $id,
				'title'    => $form->get_setting( 'title' ),
				'settings' => $form->get_settings(),
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.getFormFields`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getFormFields( $params ) {
		$deny = $this->require_forms_read();
		if ( $deny ) {
			return $deny;
		}
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$out = array();
		foreach ( \Ninja_Forms()->form( $id )->get_fields() as $f ) {
			$out[] = array(
				'id'       => (int) $f->get_id(),
				'key'      => $f->get_setting( 'key' ),
				'label'    => $f->get_setting( 'label' ),
				'type'     => $f->get_setting( 'type' ),
				'required' => (bool) $f->get_setting( 'required' ),
			);
		}
		return $this->success_response(
			array(
				'form_id' => $id,
				'fields'  => $out,
				'count'   => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.getFormActions`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getFormActions( $params ) {
		$deny = $this->require_forms_read();
		if ( $deny ) {
			return $deny;
		}
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$out = array();
		foreach ( \Ninja_Forms()->form( $id )->get_actions() as $a ) {
			$out[] = array(
				'id'     => (int) $a->get_id(),
				'type'   => $a->get_setting( 'type' ),
				'label'  => $a->get_setting( 'label' ),
				'active' => (bool) $a->get_setting( 'active' ),
			);
		}
		return $this->success_response(
			array(
				'form_id' => $id,
				'actions' => $out,
				'count'   => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.getSubmissions`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSubmissions( $params ) {
		$deny = $this->require_submissions_read();
		if ( $deny ) {
			return $deny;
		}
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$limit = $this->limit_param( $params, 50 );
		$fmap  = $this->field_map( $id );
		$subs  = \get_posts(
			array(
				'post_type'   => 'nf_sub',
				'numberposts' => $limit,
				'post_status' => 'any',
				'meta_key'    => '_form_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ninja Forms links nf_sub to its form only via _form_id meta.
				'meta_value'  => $id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- see above.
				'orderby'     => 'date',
				'order'       => 'DESC',
			)
		);
		$out   = array();
		foreach ( $subs as $s ) {
			$out[] = array(
				'submission_id' => (int) $s->ID,
				'date'          => $s->post_date,
				'values'        => $this->submission_values( $s->ID, $fmap ),
			);
		}
		return $this->success_response(
			array(
				'form_id'     => $id,
				'submissions' => $out,
				'count'       => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.getSubmission`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSubmission( $params ) {
		$deny = $this->require_submissions_read();
		if ( $deny ) {
			return $deny;
		}
		$sid = \absint( $params['submission_id'] ?? 0 );
		$sub = $sid ? \get_post( $sid ) : null;
		if ( ! $sub || 'nf_sub' !== $sub->post_type ) {
			return new \WP_Error( 'submission_not_found', 'Submission not found', array( 'status' => 404 ) );
		}
		$form_id = (int) \get_post_meta( $sid, '_form_id', true );
		$fmap    = $this->field_map( $form_id );
		return $this->success_response(
			array(
				'submission_id' => $sid,
				'form_id'       => $form_id,
				'date'          => $sub->post_date,
				'values'        => $this->submission_values( $sid, $fmap ),
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.countSubmissions`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_countSubmissions( $params ) {
		$deny = $this->require_submissions_read();
		if ( $deny ) {
			return $deny;
		}
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$q = new \WP_Query(
			array(
				'post_type'      => 'nf_sub',
				'post_status'    => 'any',
				'fields'         => 'ids',
				'meta_key'       => '_form_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ninja Forms links nf_sub to its form only via _form_id meta.
				'meta_value'     => $id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- see above.
				'posts_per_page' => 1,
			)
		);
		return $this->success_response(
			array(
				'form_id' => $id,
				'count'   => (int) $q->found_posts,
			)
		);
	}

	/**
	 * Validate an optional YYYY-MM-DD date parameter.
	 *
	 * @param array  $params Validated tool input.
	 * @param string $key    Parameter name.
	 * @return string|\WP_Error|null Date string, error, or null when absent.
	 */
	private function date_param( $params, $key ) {
		$value = isset( $params[ $key ] ) ? trim( (string) $params[ $key ] ) : '';
		if ( '' === $value ) {
			return null;
		}
		$parts = explode( '-', $value );
		if ( 3 !== count( $parts ) || ! ctype_digit( implode( '', $parts ) ) || ! checkdate( (int) $parts[1], (int) $parts[2], (int) $parts[0] ) ) {
			return new \WP_Error( 'invalid_parameter', "{$key} must be a date in YYYY-MM-DD format", array( 'status' => 400 ) );
		}
		return $value;
	}

	/**
	 * Build the shared WP_Query arguments for submission list/search/export.
	 *
	 * Ninja Forms stores submissions as `nf_sub` posts linked to their form via
	 * `_form_id` meta; active submissions are published, trashed ones are in trash.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error Query args (without paging) or error.
	 */
	private function submission_query_args( $params ) {
		$args = array(
			'post_type'  => 'nf_sub',
			'orderby'    => 'date',
			'order'      => 'DESC',
			'meta_query' => array( 'relation' => 'AND' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Ninja Forms links nf_sub to its form and values only via post meta.
		);

		$form_id = \absint( $params['form_id'] ?? 0 );
		if ( $form_id ) {
			if ( ! $this->form_exists( $form_id ) ) {
				return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
			}
			$args['meta_query'][] = array(
				'key'   => '_form_id',
				'value' => $form_id,
			);
		}

		$status = isset( $params['status'] ) ? (string) $params['status'] : 'active';
		if ( 'trash' === $status ) {
			$args['post_status'] = 'trash';
		} elseif ( 'all' === $status ) {
			$args['post_status'] = array( 'publish', 'private', 'draft', 'pending', 'future', 'trash' );
		} elseif ( 'active' === $status ) {
			$args['post_status'] = 'any';
		} else {
			return new \WP_Error( 'invalid_parameter', 'status must be active, trash or all', array( 'status' => 400 ) );
		}

		$date_query = array( 'inclusive' => true );
		foreach ( array(
			'date_from' => 'after',
			'date_to'   => 'before',
		) as $key => $bound ) {
			$date = $this->date_param( $params, $key );
			if ( \is_wp_error( $date ) ) {
				return $date;
			}
			if ( $date ) {
				$date_query[ $bound ] = 'after' === $bound ? $date . ' 00:00:00' : $date . ' 23:59:59';
			}
		}
		if ( count( $date_query ) > 1 ) {
			$args['date_query'] = array( $date_query );
		}

		$user_id = \absint( $params['user_id'] ?? 0 );
		if ( $user_id ) {
			$args['author'] = $user_id;
		}

		return $args;
	}

	/**
	 * Field map for a form, cached per call.
	 *
	 * @param int   $form_id Ninja Forms form ID.
	 * @param array $cache   Per-call cache, by reference.
	 * @return array
	 */
	private function cached_field_map( $form_id, array &$cache ) {
		if ( ! isset( $cache[ $form_id ] ) ) {
			$cache[ $form_id ] = $form_id ? $this->field_map( $form_id ) : array();
		}
		return $cache[ $form_id ];
	}

	/**
	 * Shape one nf_sub post for output.
	 *
	 * @param \WP_Post $post           Submission post.
	 * @param array    $cache          Field-map cache, by reference.
	 * @param bool     $include_values Whether to include field values.
	 * @return array
	 */
	private function submission_row( $post, array &$cache, $include_values = true ) {
		$form_id = (int) \get_post_meta( $post->ID, '_form_id', true );
		$row     = array(
			'submission_id' => (int) $post->ID,
			'form_id'       => $form_id,
			'seq_num'       => (int) \get_post_meta( $post->ID, '_seq_num', true ),
			'date'          => $post->post_date,
			'user_id'       => (int) $post->post_author,
			'status'        => 'trash' === $post->post_status ? 'trash' : 'active',
		);
		if ( $include_values ) {
			$row['values'] = $this->submission_values( $post->ID, $this->cached_field_map( $form_id, $cache ) );
		}
		return $row;
	}

	/**
	 * Flatten a stored field value (string or list) to text.
	 *
	 * @param mixed $value Stored value.
	 * @return string
	 */
	private function value_text( $value ) {
		if ( is_array( $value ) ) {
			return implode( ', ', array_map( array( $this, 'value_text' ), $value ) );
		}
		return is_scalar( $value ) ? (string) $value : '';
	}

	/**
	 * Read the page size and page number from the input.
	 *
	 * @param array $params Validated tool input.
	 * @return array{0:int,1:int} Limit and page.
	 */
	private function paging( $params ) {
		$limit = min( 200, $this->limit_param( $params, 50 ) );
		$page  = max( 1, \absint( $params['page'] ?? 1 ) );
		return array( $limit, $page );
	}

	/**
	 * Tool handler for `ninjaforms.listSubmissions`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_listSubmissions( $params ) {
		$deny = $this->require_submissions_read();
		if ( $deny ) {
			return $deny;
		}
		$args = $this->submission_query_args( $params );
		if ( \is_wp_error( $args ) ) {
			return $args;
		}
		list( $limit, $page )   = $this->paging( $params );
		$args['posts_per_page'] = $limit;
		$args['paged']          = $page;
		$include_values         = ! isset( $params['include_values'] ) || \rest_sanitize_boolean( $params['include_values'] );

		$q     = new \WP_Query( $args );
		$cache = array();
		$out   = array();
		foreach ( $q->posts as $post ) {
			$out[] = $this->submission_row( $post, $cache, $include_values );
		}
		return $this->success_response(
			array(
				'submissions' => $out,
				'count'       => count( $out ),
				'total'       => (int) $q->found_posts,
				'page'        => $page,
				'total_pages' => (int) $q->max_num_pages,
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.searchSubmissions`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_searchSubmissions( $params ) {
		$deny = $this->require_submissions_read();
		if ( $deny ) {
			return $deny;
		}
		$term = trim( \sanitize_text_field( $params['query'] ?? '' ) );
		if ( mb_strlen( $term ) < 2 ) {
			return new \WP_Error( 'invalid_parameter', 'query must be at least 2 characters', array( 'status' => 400 ) );
		}
		$args = $this->submission_query_args( $params );
		if ( \is_wp_error( $args ) ) {
			return $args;
		}
		// Any `_field_{id}` meta row whose value contains the term (WP escapes both LIKE patterns).
		$args['meta_query'][]   = array(
			'key'         => '_field_',
			'compare_key' => 'LIKE',
			'value'       => $term,
			'compare'     => 'LIKE',
		);
		list( $limit, $page )   = $this->paging( $params );
		$args['posts_per_page'] = $limit;
		$args['paged']          = $page;

		$q     = new \WP_Query( $args );
		$cache = array();
		$out   = array();
		foreach ( $q->posts as $post ) {
			$row     = $this->submission_row( $post, $cache, true );
			$matched = array();
			foreach ( $row['values'] as $key => $value ) {
				if ( false !== mb_stripos( $this->value_text( $value ), $term ) ) {
					$matched[] = $key;
				}
			}
			$row['matched_fields'] = $matched;
			$out[]                 = $row;
		}
		return $this->success_response(
			array(
				'query'       => $term,
				'submissions' => $out,
				'count'       => count( $out ),
				'total'       => (int) $q->found_posts,
				'page'        => $page,
				'total_pages' => (int) $q->max_num_pages,
			)
		);
	}

	/**
	 * Neutralise spreadsheet formula injection in a CSV cell.
	 *
	 * @param string $cell Cell text.
	 * @return string
	 */
	private function csv_safe( $cell ) {
		if ( '' !== $cell && in_array( $cell[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			return "'" . $cell;
		}
		return $cell;
	}

	/**
	 * Tool handler for `ninjaforms.exportSubmissions`.
	 *
	 * Builds the CSV in memory and returns it inline: Ninja Forms' own export
	 * writes the file under wp-content/uploads, a public path, which this tool
	 * deliberately avoids for personal data.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_exportSubmissions( $params ) {
		$deny = $this->require_submissions_read();
		if ( $deny ) {
			return $deny;
		}
		$form_id = \absint( $params['form_id'] ?? 0 );
		if ( ! $form_id || ! $this->form_exists( $form_id ) ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$args = $this->submission_query_args( $params );
		if ( \is_wp_error( $args ) ) {
			return $args;
		}
		$limit                  = min( 2000, $this->limit_param( $params, 500 ) );
		$args['posts_per_page'] = $limit;
		$args['order']          = 'ASC';

		$fmap    = $this->field_map( $form_id );
		$columns = array();
		foreach ( $fmap as $fid => $meta ) {
			if ( in_array( $meta['type'], array( 'submit', 'html', 'hr', 'recaptcha', 'spam' ), true ) ) {
				continue;
			}
			$columns[ $meta['key'] ? $meta['key'] : 'field_' . $fid ] = $meta['label'];
		}
		if ( ! empty( $params['field_keys'] ) && is_array( $params['field_keys'] ) ) {
			$wanted  = array_map( 'sanitize_key', $params['field_keys'] );
			$unknown = array_values( array_diff( $wanted, array_keys( $columns ) ) );
			if ( $unknown ) {
				return new \WP_Error( 'invalid_parameter', 'Unknown field_keys: ' . implode( ', ', $unknown ), array( 'status' => 400 ) );
			}
			$columns = array_intersect_key( $columns, array_flip( $wanted ) );
		}

		$q   = new \WP_Query( $args );
		$fh  = fopen( 'php://temp', 'r+' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- in-memory stream for fputcsv, nothing touches disk.
		$hdr = array_merge( array( '#', 'Date' ), array_values( $columns ) );
		fputcsv( $fh, array_map( array( $this, 'csv_safe' ), $hdr ) );
		foreach ( $q->posts as $post ) {
			$values = $this->submission_values( $post->ID, $fmap );
			$line   = array( (string) \get_post_meta( $post->ID, '_seq_num', true ), $post->post_date );
			foreach ( array_keys( $columns ) as $key ) {
				$line[] = $this->value_text( $values[ $key ] ?? '' );
			}
			fputcsv( $fh, array_map( array( $this, 'csv_safe' ), $line ) );
		}
		rewind( $fh );
		$csv = stream_get_contents( $fh );
		fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- closes the in-memory stream above.

		return $this->success_response(
			array(
				'form_id'   => $form_id,
				'format'    => 'csv',
				'filename'  => 'ninja-forms-' . $form_id . '-submissions.csv',
				'columns'   => array_merge( array( '#', 'Date' ), array_values( $columns ) ),
				'rows'      => count( $q->posts ),
				'total'     => (int) $q->found_posts,
				'truncated' => (int) $q->found_posts > count( $q->posts ),
				'csv'       => $csv,
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.getField`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getField( $params ) {
		$deny = $this->require_forms_read();
		if ( $deny ) {
			return $deny;
		}
		$form_id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $form_id ) ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$field_id  = \absint( $params['field_id'] ?? 0 );
		$field_key = isset( $params['field_key'] ) ? \sanitize_key( $params['field_key'] ) : '';
		if ( ! $field_id && '' === $field_key ) {
			return new \WP_Error( 'missing_parameter', 'field_id or field_key required', array( 'status' => 400 ) );
		}
		$found = null;
		foreach ( \Ninja_Forms()->form( $form_id )->get_fields() as $f ) {
			if ( ( $field_id && (int) $f->get_id() === $field_id ) || ( ! $field_id && $f->get_setting( 'key' ) === $field_key ) ) {
				$found = $f;
				break;
			}
		}
		if ( ! $found ) {
			return new \WP_Error( 'field_not_found', 'Field not found on this form', array( 'status' => 404 ) );
		}
		$settings = $found->get_settings();
		if ( empty( $params['include_styles'] ) || ! \rest_sanitize_boolean( $params['include_styles'] ) ) {
			foreach ( array_keys( $settings ) as $k ) {
				if ( false !== strpos( $k, '_styles_' ) ) {
					unset( $settings[ $k ] );
				}
			}
		}
		return $this->success_response(
			array(
				'form_id'  => $form_id,
				'id'       => (int) $found->get_id(),
				'key'      => $found->get_setting( 'key' ),
				'label'    => $found->get_setting( 'label' ),
				'type'     => $found->get_setting( 'type' ),
				'required' => (bool) $found->get_setting( 'required' ),
				'settings' => $settings,
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.listNotifications`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_listNotifications( $params ) {
		$deny = $this->require_forms_read();
		if ( $deny ) {
			return $deny;
		}
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$out = array();
		foreach ( \Ninja_Forms()->form( $id )->get_actions() as $a ) {
			if ( 'email' !== $a->get_setting( 'type' ) ) {
				continue;
			}
			$out[] = array(
				'id'           => (int) $a->get_id(),
				'label'        => $a->get_setting( 'label' ),
				'active'       => (bool) $a->get_setting( 'active' ),
				'to'           => $a->get_setting( 'to' ),
				'subject'      => $a->get_setting( 'email_subject' ),
				'message'      => $a->get_setting( 'email_message' ),
				'format'       => $a->get_setting( 'email_format' ),
				'from_name'    => $a->get_setting( 'from_name' ),
				'from_address' => $a->get_setting( 'from_address' ),
				'reply_to'     => $a->get_setting( 'reply_to' ),
				'cc'           => $a->get_setting( 'cc' ),
				'bcc'          => $a->get_setting( 'bcc' ),
				'attach_csv'   => (bool) $a->get_setting( 'attach_csv' ),
				'conditions'   => $a->get_setting( 'conditions' ),
			);
		}
		return $this->success_response(
			array(
				'form_id'       => $id,
				'notifications' => $out,
				'count'         => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.getFormEmbedCode`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getFormEmbedCode( $params ) {
		$deny = $this->require_forms_read();
		if ( $deny ) {
			return $deny;
		}
		$id   = \absint( $params['form_id'] ?? 0 );
		$form = $this->form_exists( $id );
		if ( ! $form ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		return $this->success_response(
			array(
				'form_id'   => $id,
				'title'     => $form->get_setting( 'title' ),
				'shortcode' => '[ninja_form id=' . $id . ']',
				'block'     => '<!-- wp:ninja-forms/form {"formID":' . $id . '} /-->',
				'php'       => '<?php Ninja_Forms()->display( ' . $id . ' ); ?>',
				'note'      => 'Ninja Forms has no standalone JavaScript embed; use the shortcode in classic content/widgets or the block in the block editor.',
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.getCalculations`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getCalculations( $params ) {
		$deny = $this->require_forms_read();
		if ( $deny ) {
			return $deny;
		}
		$id   = \absint( $params['form_id'] ?? 0 );
		$form = $this->form_exists( $id );
		if ( ! $form ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$calcs = array();
		$raw   = $form->get_setting( 'calculations' );
		foreach ( is_array( $raw ) ? $raw : array() as $c ) {
			$calcs[] = array(
				'name'     => $c['name'] ?? '',
				'equation' => $c['eq'] ?? '',
				'decimals' => $c['dec'] ?? '',
			);
		}
		$fields = array();
		foreach ( \Ninja_Forms()->form( $id )->get_fields() as $f ) {
			$s    = $f->get_settings();
			$info = array();
			if ( ! empty( $s['calc_auto_include'] ) ) {
				$info['auto_included'] = true;
			}
			if ( isset( $s['calc_value'] ) && '' !== $s['calc_value'] ) {
				$info['calc_value'] = $s['calc_value'];
			}
			if ( ! empty( $s['options'] ) && is_array( $s['options'] ) ) {
				$opts = array();
				foreach ( $s['options'] as $o ) {
					if ( isset( $o['calc'] ) && '' !== (string) $o['calc'] ) {
						$opts[] = array(
							'label' => $o['label'] ?? '',
							'calc'  => $o['calc'],
						);
					}
				}
				if ( $opts ) {
					$info['option_calc_values'] = $opts;
				}
			}
			$refs = array();
			foreach ( array( 'default', 'default_value', 'value' ) as $k ) {
				if ( isset( $s[ $k ] ) && is_string( $s[ $k ] ) && false !== strpos( $s[ $k ], '{calc:' ) ) {
					$refs[] = $s[ $k ];
				}
			}
			if ( $refs ) {
				$info['displays'] = $refs;
			}
			if ( $info ) {
				$fields[] = array_merge(
					array(
						'id'    => (int) $f->get_id(),
						'key'   => $s['key'] ?? '',
						'label' => $s['label'] ?? '',
						'type'  => $s['type'] ?? '',
					),
					$info
				);
			}
		}
		return $this->success_response(
			array(
				'form_id'      => $id,
				'calculations' => $calcs,
				'count'        => count( $calcs ),
				'calc_fields'  => $fields,
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.updateFormTitle`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_updateFormTitle( $params ) {
		$deny = $this->require_cap( 'manage_options' );
		if ( $deny ) {
			return $deny;
		}
		$id   = \absint( $params['form_id'] ?? 0 );
		$form = $this->form_exists( $id );
		if ( ! $form ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$title = \sanitize_text_field( $params['title'] ?? '' );
		if ( '' === $title ) {
			return new \WP_Error( 'missing_parameter', 'title required', array( 'status' => 400 ) );
		}
		$form->update_setting( 'title', $title )->save();
		return $this->success_response(
			array(
				'form_id' => $id,
				'title'   => $title,
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.deleteSubmission`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_deleteSubmission( $params ) {
		$deny = $this->require_cap( 'manage_options' );
		if ( $deny ) {
			return $deny;
		}
		$sid = \absint( $params['submission_id'] ?? 0 );
		$sub = $sid ? \get_post( $sid ) : null;
		if ( ! $sub || 'nf_sub' !== $sub->post_type ) {
			return new \WP_Error( 'submission_not_found', 'Submission not found', array( 'status' => 404 ) );
		}
		$ok = \wp_delete_post( $sid, true );
		return $this->success_response(
			array(
				'submission_id' => $sid,
				'deleted'       => (bool) $ok,
			)
		);
	}

	/**
	 * Tool handler for `ninjaforms.duplicateForm`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_duplicateForm( $params ) {
		$deny = $this->require_cap( 'manage_options' );
		if ( $deny ) {
			return $deny;
		}
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) {
			return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		}
		$new_id = null;
		try {
			$new_id = \Ninja_Forms()->form( $id )->duplicate();
		} catch ( \Throwable $e ) {
			$new_id = null;
		}
		if ( ! $new_id ) {
			return new \WP_Error( 'duplicate_failed', 'Ninja Forms duplicate API unavailable in this version', array( 'status' => 501 ) );
		}
		return $this->success_response(
			array(
				'source_form_id' => $id,
				'new_form_id'    => (int) $new_id,
			)
		);
	}
}
