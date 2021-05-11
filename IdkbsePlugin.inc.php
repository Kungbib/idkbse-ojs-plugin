<?php

/**
 * @file IdkbsePlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University Library
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class idkbsePlugin
 * @ingroup plugins_generic_idkbse
 * @brief Id.kb.se plugin class
 *
 *
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class IdkbsePlugin extends GenericPlugin {

   function getName() {
        return 'idkbsePlugin';
    }

    function getDisplayName() {
        return "Id.kb.se keywords";
    }

    function getDescription() {
        return "Integrates swedish subject headings from id.kb.se with the keyword field.";
    }

    function register($category, $path, $mainContextId = NULL) {
		$success = parent::register($category, $path);
		if ($success && $this->getEnabled()) {
				HookRegistry::register('TemplateResource::getFilename', array($this, 'handleFormDisplay'));
        }
		return $success;
	}

	function handleFormDisplay($hookName, $args) {
		$request = PKPApplication::getRequest();
		$templateMgr = TemplateManager::getManager($request);
		$template =& $args[1];
		switch ($template) {
			case 'submission/submissionMetadataFormFields.tpl':
				$templateMgr->registerFilter("output", array($this, 'keywordsFilter'));
				break;
		}
		return false;
	}

	/**
	 * Output filter adds id.kb.se terms to the keyword field by overriding the existing controlled vocabulary settings
	 * @param $output string
	 * @param $templateMgr TemplateManager
	 * @return $string
	 */
	function keywordsFilter($output, $templateMgr) {

		$endPoint = '</script>';

		$startPoint = 'sv_SE-keywords\]\[\]",';
		$newscript = $this->idkbseTagit();
		$output = preg_replace('#('.$startPoint.')(.*?)('.$endPoint.')#si', '$1'.$newscript.'$3', $output, 1);		

		if (stristr($output, '-keywords][]')){
			$templateMgr->unregisterFilter('output', array($this, 'keywordsFilter'));
		}

		return $output;
	}

	/**
	 * Get tagit settings
	 * @return $string
	 */
	function idkbseTagit(){
		return "allowSpaces: true,
				placeholderText: 'Lägg till svenska termer',
				tagSource: function(request, response) {
						$.ajax({
							url: 'https://id.kb.se/find?&inScheme.@id=https://id.kb.se/term/sao&_limit=15',
							dataType: 'json',
							cache: 'true',
							data: {
								q: request.term + '*'
							},
							success: 
										function( data ) {
										var output = data.items;
										response($.map(data.items, function(item) {
											return {
												label: item.prefLabel + ' [' + item['@id'] + ']',
												value: item.prefLabel+'|'+item['@id']
											}
										}));
							}	
							
						});
				},
	
				beforeTagAdded: function(event, ui) {
				if (ui.tagLabel.includes('id.kb.se/term')) {
					var splitValue = ui.tagLabel.split(\"|\")
					var prefLabel = splitValue.shift();
					var id = splitValue.pop();
					
					var labelSpan = ui.tag[0].childNodes[0];
					labelSpan.textContent = '';
					var link = document.createElement(\"a\");
					link.setAttribute(\"href\", id);
					link.setAttribute('target', \"_blank\")
					link.innerHTML = prefLabel;
					labelSpan.appendChild(link);
				}
				},
			});

		});";
	}
}
?>
