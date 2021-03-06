<?php

add_action( 'init', array($this->_hook, 'init') );
add_action( 'admin_init', array($this->_hook, 'adminInit') );
// add_action( 'parse_request', array($this->_hook, 'parseRequest') );
add_action( 'template_redirect', array($this->_hook, 'templateRedirect') );
add_action( 'admin_menu', array($this->_hook, 'adminMenu') );

add_action( 'wp_footer', array($this->_hook, 'wpFooter') );
add_action( 'wp_enqueue_scripts', array($this->_hook, 'wpEnqueueScripts') );

add_action( 'wp_ajax_'.$this->getConfig('prefix').'pagination_history', array($this->_hook, 'paginationHistory') );
add_action( 'wp_ajax_nopriv_'.$this->getConfig('prefix').'pagination_history', array($this->_hook, 'paginationHistory') );

add_filter( 'generate_rewrite_rules', array($this->_hook, 'filterGenerateRewriteRules') );
add_filter( 'rewrite_rules_array', array($this->_hook, 'filterRewriteRulesArray') );
add_filter( 'query_vars', array($this->_hook, 'filterQueryVars') );