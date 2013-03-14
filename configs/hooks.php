<?php

add_action( 'init', array($this->_hook, 'init') );
add_action( 'admin_init', array($this->_hook, 'adminInit') );
// add_action( 'parse_request', array($this->_hook, 'parseRequest') );
add_action( 'template_redirect', array($this->_hook, 'templateRedirect') );

add_action( 'admin_menu', array($this->_hook, 'adminMenu') );

add_filter( 'generate_rewrite_rules', array($this->_hook, 'filterGenerateRewriteRules') );
add_filter( 'rewrite_rules_array', array($this->_hook, 'filterRewriteRulesArray') );
add_filter( 'query_vars', array($this->_hook, 'filterQueryVars') );