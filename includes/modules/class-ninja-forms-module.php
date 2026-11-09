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

class NinjaForms_Module extends Module_Base {

	protected $module_name = 'ninjaforms';

	protected function register_tools() {
		// ── Reads (free) ──
		$this->register_tool( 'listForms', array(
			'description'  => 'List all Ninja Forms (id, title, field count).',
			'input_schema' => array( 'type' => 'object', 'properties' => (object) array() ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getForm', array(
			'description'  => 'Get a Ninja Forms form with its settings.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'form_id' ), 'properties' => array( 'form_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getFormFields', array(
			'description'  => 'List the fields of a form (key, type, label, required).',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'form_id' ), 'properties' => array( 'form_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getFormActions', array(
			'description'  => 'List the actions (email, store submission, redirect…) configured on a form.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'form_id' ), 'properties' => array( 'form_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getSubmissions', array(
			'description'  => 'List submissions of a form with their field values.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'form_id' ), 'properties' => array(
				'form_id' => array( 'type' => 'integer' ), 'limit' => array( 'type' => 'integer' ),
			) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getSubmission', array(
			'description'  => 'Get a single submission with its field values.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'submission_id' ), 'properties' => array( 'submission_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'countSubmissions', array(
			'description'  => 'Count submissions of a form.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'form_id' ), 'properties' => array( 'form_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );

		// ── Writes (pro) ──
		$this->register_tool( 'updateFormTitle', array(
			'description'  => 'Rename a Ninja Forms form.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'form_id', 'title' ), 'properties' => array(
				'form_id' => array( 'type' => 'integer' ), 'title' => array( 'type' => 'string' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'deleteSubmission', array(
			'description'  => 'Delete a form submission.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'submission_id' ), 'properties' => array( 'submission_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'duplicateForm', array(
			'description'  => 'Duplicate an existing form.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'form_id' ), 'properties' => array( 'form_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'write',
		) );
	}

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

	private function form_exists( $form_id ) {
		$form = \Ninja_Forms()->form( $form_id )->get();
		return ( $form && $form->get_id() ) ? $form : null;
	}

	private function field_map( $form_id ) {
		$map = array();
		foreach ( \Ninja_Forms()->form( $form_id )->get_fields() as $f ) {
			$map[ (int) $f->get_id() ] = array( 'key' => $f->get_setting( 'key' ), 'label' => $f->get_setting( 'label' ), 'type' => $f->get_setting( 'type' ) );
		}
		return $map;
	}

	private function submission_values( $sub_id, array $fmap ) {
		$values = array();
		foreach ( $fmap as $fid => $meta ) {
			$v = \get_post_meta( $sub_id, '_field_' . $fid, true );
			if ( $v !== '' ) {
				$values[ $meta['key'] ?: ( 'field_' . $fid ) ] = $v;
			}
		}
		return $values;
	}

	public function execute_listForms( $params ) {
		$deny = $this->require_forms_read(); if ( $deny ) return $deny;
		$out = array();
		foreach ( \Ninja_Forms()->form()->get_forms() as $form ) {
			$id = (int) $form->get_id();
			$out[] = array(
				'id'     => $id,
				'title'  => $form->get_setting( 'title' ),
				'fields' => count( \Ninja_Forms()->form( $id )->get_fields() ),
			);
		}
		return $this->success_response( array( 'forms' => $out, 'count' => count( $out ) ) );
	}

	public function execute_getForm( $params ) {
		$deny = $this->require_forms_read(); if ( $deny ) return $deny;
		$id = \absint( $params['form_id'] ?? 0 );
		$form = $this->form_exists( $id );
		if ( ! $form ) return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		return $this->success_response( array(
			'id'       => $id,
			'title'    => $form->get_setting( 'title' ),
			'settings' => $form->get_settings(),
		) );
	}

	public function execute_getFormFields( $params ) {
		$deny = $this->require_forms_read(); if ( $deny ) return $deny;
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
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
		return $this->success_response( array( 'form_id' => $id, 'fields' => $out, 'count' => count( $out ) ) );
	}

	public function execute_getFormActions( $params ) {
		$deny = $this->require_forms_read(); if ( $deny ) return $deny;
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		$out = array();
		foreach ( \Ninja_Forms()->form( $id )->get_actions() as $a ) {
			$out[] = array(
				'id'    => (int) $a->get_id(),
				'type'  => $a->get_setting( 'type' ),
				'label' => $a->get_setting( 'label' ),
				'active' => (bool) $a->get_setting( 'active' ),
			);
		}
		return $this->success_response( array( 'form_id' => $id, 'actions' => $out, 'count' => count( $out ) ) );
	}

	public function execute_getSubmissions( $params ) {
		$deny = $this->require_submissions_read(); if ( $deny ) return $deny;
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		$limit = \absint( $params['limit'] ?? 50 ) ?: 50;
		$fmap = $this->field_map( $id );
		$subs = \get_posts( array(
			'post_type' => 'nf_sub', 'numberposts' => $limit, 'post_status' => 'any',
			'meta_key' => '_form_id', 'meta_value' => $id, 'orderby' => 'date', 'order' => 'DESC',
		) );
		$out = array();
		foreach ( $subs as $s ) {
			$out[] = array(
				'submission_id' => (int) $s->ID,
				'date'          => $s->post_date,
				'values'        => $this->submission_values( $s->ID, $fmap ),
			);
		}
		return $this->success_response( array( 'form_id' => $id, 'submissions' => $out, 'count' => count( $out ) ) );
	}

	public function execute_getSubmission( $params ) {
		$deny = $this->require_submissions_read(); if ( $deny ) return $deny;
		$sid = \absint( $params['submission_id'] ?? 0 );
		$sub = $sid ? \get_post( $sid ) : null;
		if ( ! $sub || $sub->post_type !== 'nf_sub' ) return new \WP_Error( 'submission_not_found', 'Submission not found', array( 'status' => 404 ) );
		$form_id = (int) \get_post_meta( $sid, '_form_id', true );
		$fmap = $this->field_map( $form_id );
		return $this->success_response( array(
			'submission_id' => $sid,
			'form_id'       => $form_id,
			'date'          => $sub->post_date,
			'values'        => $this->submission_values( $sid, $fmap ),
		) );
	}

	public function execute_countSubmissions( $params ) {
		$deny = $this->require_submissions_read(); if ( $deny ) return $deny;
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		$q = new \WP_Query( array(
			'post_type' => 'nf_sub', 'post_status' => 'any', 'fields' => 'ids',
			'meta_key' => '_form_id', 'meta_value' => $id, 'posts_per_page' => 1,
		) );
		return $this->success_response( array( 'form_id' => $id, 'count' => (int) $q->found_posts ) );
	}

	public function execute_updateFormTitle( $params ) {
		$deny = $this->require_cap( 'manage_options' ); if ( $deny ) return $deny;
		$id = \absint( $params['form_id'] ?? 0 );
		$form = $this->form_exists( $id );
		if ( ! $form ) return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		$title = \sanitize_text_field( $params['title'] ?? '' );
		if ( $title === '' ) return new \WP_Error( 'missing_parameter', 'title required', array( 'status' => 400 ) );
		$form->update_setting( 'title', $title )->save();
		return $this->success_response( array( 'form_id' => $id, 'title' => $title ) );
	}

	public function execute_deleteSubmission( $params ) {
		$deny = $this->require_cap( 'manage_options' ); if ( $deny ) return $deny;
		$sid = \absint( $params['submission_id'] ?? 0 );
		$sub = $sid ? \get_post( $sid ) : null;
		if ( ! $sub || $sub->post_type !== 'nf_sub' ) return new \WP_Error( 'submission_not_found', 'Submission not found', array( 'status' => 404 ) );
		$ok = \wp_delete_post( $sid, true );
		return $this->success_response( array( 'submission_id' => $sid, 'deleted' => (bool) $ok ) );
	}

	public function execute_duplicateForm( $params ) {
		$deny = $this->require_cap( 'manage_options' ); if ( $deny ) return $deny;
		$id = \absint( $params['form_id'] ?? 0 );
		if ( ! $this->form_exists( $id ) ) return new \WP_Error( 'form_not_found', 'Form not found', array( 'status' => 404 ) );
		$new_id = null;
		try {
			$new_id = \Ninja_Forms()->form( $id )->duplicate();
		} catch ( \Throwable $e ) {
			$new_id = null;
		}
		if ( ! $new_id ) {
			return new \WP_Error( 'duplicate_failed', 'Ninja Forms duplicate API unavailable in this version', array( 'status' => 501 ) );
		}
		return $this->success_response( array( 'source_form_id' => $id, 'new_form_id' => (int) $new_id ) );
	}
}
