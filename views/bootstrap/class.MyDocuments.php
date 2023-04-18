<?php
/**
 * Implementation of MyDocuments view
 *
 * @category   DMS
 * @package    SeedDMS
 * @license    GPL 2
 * @version    @version@
 * @author     Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */

/**
 * Include parent class
 */
require_once("class.Bootstrap.php");

/**
 * Include class to preview documents
 */
require_once("SeedDMS/Preview.php");

/**
 * Class which outputs the html page for MyDocuments view
 *
 * @category   DMS
 * @package    SeedDMS
 * @author     Markus Westphal, Malcolm Cowe, Uwe Steinmann <uwe@steinmann.cx>
 * @copyright  Copyright (C) 2002-2005 Markus Westphal,
 *             2006-2008 Malcolm Cowe, 2010 Matteo Lucarelli,
 *             2010-2012 Uwe Steinmann
 * @version    Release: @package_version@
 */
class SeedDMS_View_MyDocuments extends SeedDMS_Bootstrap_Style {

	function js() { /* {{{ */
		header('Content-Type: application/javascript');
		parent::jsTranslations(array('cancel', 'splash_move_document', 'confirm_move_document', 'move_document', 'confirm_transfer_link_document', 'transfer_content', 'link_document', 'splash_move_folder', 'confirm_move_folder', 'move_folder'));
		$this->printClickDocumentJs();
?>
$(document).ready( function() {
	$('body').on('click', 'ul.bs-docs-sidenav li a', function(ev){
		ev.preventDefault();
		$('#kkkk.ajax').data('action', $(this).data('action'));
		$('#kkkk.ajax').trigger('update', {orderby: $(this).data('orderby')});
	});
	$('body').on('click', 'table th a', function(ev){
		ev.preventDefault();
		$('#kkkk.ajax').data('action', $(this).data('action'));
		$('#kkkk.ajax').trigger('update', {orderby: $(this).data('orderby'), orderdir: $(this).data('orderdir')});
	});
});
<?php
	} /* }}} */

	protected function printListHeader($resArr, $previewer, $order=false) { /* {{{ */
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];

		print "<table class=\"table table-condensed\">";
		print "<thead>\n<tr>\n";
		print "<th></th>\n";
		if($order)
			print "<th><a data-action=\"".$order."\" data-orderby=\"n\" data-orderdir=\"".($orderdir == 'desc' ? '' : 'desc')."\">".getMLText("name")."</a> ".($orderby == 'n' || $orderby == '' ? ($orderdir == 'desc' ? '<i class="fa fa-arrow-up"></i>' :  '<i class="fa fa-arrow-down"></i>') : '')." &middot; <a data-action=\"".$order."\" data-orderby=\"u\" data-orderdir=\"".($orderdir == 'desc' ? '' : 'desc')."\">".getMLText("last_update")."</a> ".($orderby == 'u' ? ($orderdir == 'desc' ? '<i class="fa fa-arrow-up"></i>' :  '<i class="fa fa-arrow-down"></i>') : '')." &middot; <a data-action=\"".$order."\" data-orderby=\"e\" data-orderdir=\"".($orderdir == 'desc' ? '' : 'desc')."\">".getMLText("expires")."</a> ".($orderby == 'e' ? ($orderdir == 'desc' ? '<i class="fa fa-arrow-up"></i>' :  '<i class="fa fa-arrow-down"></i>') : '')."</th>\n";
		else
			print "<th>".getMLText("name")."</th>\n";
		if($order)
			print "<th><a data-action=\"".$order."\" data-orderby=\"s\" data-orderdir=\"".($orderdir == 'desc' ? '' : 'desc')."\">".getMLText("status")."</a>".($orderby == 's' ? " ".($orderdir == 'desc' ? '<i class="fa fa-arrow-up"></i>' :  '<i class="fa fa-arrow-down"></i>') : '')."</th>\n";
		else
			print "<th>".getMLText("status")."</th>\n";
		print "<th>".getMLText("action")."</th>\n";
		print "</tr>\n</thead>\n<tbody>\n";
	} /* }}} */

	protected function printListFooter() { /* {{{ */
		echo "</tbody>\n</table>";
	} /* }}} */

	protected function printList($resArr, $previewer, $order=false) { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];

		$this->printListHeader($resArr, $previewer, $order);
		$noaccess = 0;
		foreach ($resArr as $res) {
			$document = $dms->getDocument($res["id"]);
			$document->verifyLastestContentExpriry();

			if($document->getAccessMode($user) >= M_READ && $document->getLatestContent()) {
				$txt = $this->callHook('documentListItem', $document, $previewer, false, $res['version']);
				if(is_string($txt))
					echo $txt;
				else
					echo $this->documentListRow($document, $previewer, false, $res['version']);
			} else {
				$noaccess++;
			}
		}
		$this->printListFooter();

		if($noaccess) {
			$this->warningMsg(getMLText('list_contains_no_access_docs', array('count'=>$noaccess)));
		}
	} /* }}} */

	function listReviews() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$db = $dms->getDB();
		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		$resArr = $dms->getDocumentList('ReviewByMe', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_to_review"));
		if($resArr) {
			$this->printList($resArr, $previewer, 'listReviews');
		} else {
			printMLText("no_docs_to_review");
		}

	} /* }}} */

	function listApprovals() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		$resArr = $dms->getDocumentList('ApproveByMe', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}
		$this->contentHeading(getMLText("documents_to_approve"));
		if($resArr) {
			$this->printList($resArr, $previewer, 'listApprovals');
		} else {
			printMLText("no_docs_to_approve");
		}
	} /* }}} */

	function listDocsToLookAt() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$workflowmode = $this->params['workflowmode'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		if($workflowmode != 'advanced') {
			/* Get list of documents owned by current user that are
			 * pending review or pending approval.
			 */
			$resArr = $dms->getDocumentList('AppRevOwner', $user, false, $orderby, $orderdir);
			if (is_bool($resArr) && !$resArr) {
				$this->contentHeading(getMLText("warning"));
				$this->contentContainer(getMLText("internal_error_exit"));
				$this->htmlEndPage();
				exit;
			}

			$this->contentHeading(getMLText("documents_user_requiring_attention"));
			if ($resArr) {
				$this->printList($resArr, $previewer, 'listDocsToLookAt');
			} else {
				printMLText("no_docs_to_look_at");
			}
		} else {
			$resArr = $dms->getDocumentList('WorkflowOwner', $user, false, $orderby, $orderdir);
			if (is_bool($resArr) && !$resArr) {
				$this->contentHeading(getMLText("warning"));
				$this->contentContainer("Internal error. Unable to complete request. Exiting.");
				$this->htmlEndPage();
				exit;
			}

			$this->contentHeading(getMLText("documents_user_requiring_attention"));
			if($resArr) {
				$this->printList($resArr, $previewer);
			}
			else printMLText("no_docs_to_look_at");
		}
	} /* }}} */

	function listReceiveOwner() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$showtree = $this->params['showtree'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of documents owned by current user */
		$resArr = $dms->getDocumentList('ReceiveOwner', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_user_reception"));
		if($resArr) {
			$this->printList($resArr, $previewer, 'listReceiveOwner');
		}
		else printMLText("empty_notify_list");
	} /* }}} */

	function listNoReceiveOwner() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$showtree = $this->params['showtree'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of documents owned by current user */
		$resArr = $dms->getDocumentList('NoReceiveOwner', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_user_no_reception"));
		if($resArr) {
			$this->printList($resArr, $previewer, 'listNoReceiveOwner');
		}
		else printMLText("empty_notify_list");
	} /* }}} */

	function listMyDocs() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$showtree = $this->params['showtree'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of documents owned by current user */
		$resArr = $dms->getDocumentList('MyDocs', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("all_documents"));
		if($resArr) {
			$this->printList($resArr, $previewer, 'listMyDocs');
		}
		else printMLText("empty_notify_list");
	} /* }}} */

	function listWorkflow() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		// Get document list for the current user.
		$workflowStatus = $user->getWorkflowStatus();

		$resArr = $dms->getDocumentList('WorkflowByMe', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		if (count($resArr)>0) {
			// Create an array to hold all of these results, and index the array by
			// document id. This makes it easier to retrieve document ID information
			// later on and saves us having to repeatedly poll the database every time
			// new document information is required.
			$docIdx = array();
			foreach ($resArr as $res) {
				$docIdx[$res["id"]][$res["version"]] = $res;
			}

			// List the documents where a review has been requested.
			$this->contentHeading(getMLText("documents_to_process"));

			$printheader=true;
			$iRev = array();
			$dList = array();
			foreach ($workflowStatus["u"] as $st) {

				if ( isset($docIdx[$st["document"]][$st["version"]]) && !in_array($st["document"], $dList) ) {
					$dList[] = $st["document"];
					$document = $dms->getDocument($st["document"]);
					$document->verifyLastestContentExpriry();

					if ($printheader){
						print "<table class=\"table table-condensed\">";
						print "<thead>\n<tr>\n";
						print "<th></th>\n";
						print "<th>".getMLText("name")."</th>\n";
						print "<th>".getMLText("status")."</th>\n";
						print "<th>".getMLText("action")."</th>\n";
						print "</tr>\n</thead>\n<tbody>\n";
						$printheader=false;
					}

					$txt = $this->callHook('documentListItem', $document, $previewer);
					if(is_string($txt))
						echo $txt;
					else {
						echo $this->documentListRow($document, $previewer, false, $st['version']);
					}
				}
			}
			foreach ($workflowStatus["g"] as $st) {

				if (!in_array($st["document"], $iRev) && isset($docIdx[$st["document"]][$st["version"]]) && !in_array($st["document"], $dList) /* && $docIdx[$st["documentID"]][$st["version"]]['owner'] != $user->getId() */) {
					$dList[] = $st["document"];
					$document = $dms->getDocument($st["document"]);
					$document->verifyLastestContentExpriry();

					if ($printheader){
						print "<table class=\"table table-condensed\">";
						print "<thead>\n<tr>\n";
						print "<th></th>\n";
						print "<th>".getMLText("name")."</th>\n";
						print "<th>".getMLText("status")."</th>\n";
						print "<th>".getMLText("action")."</th>\n";
						print "</tr>\n</thead>\n<tbody>\n";
						$printheader=false;
					}

					$txt = $this->callHook('documentListItem', $document, $previewer);
					if(is_string($txt))
						echo $txt;
					else {
						echo $this->documentListRow($document, $previewer, false, $st['version']);
					}
				}
			}
			if (!$printheader){
				echo "</tbody>\n</table>";
			}else{
				printMLText("no_docs_to_check");
			}
		}

	} /* }}} */

	function listRevisions() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		// Get document list for the current user.
		$revisionStatus = $user->getRevisionStatus();

		$resArr = $dms->getDocumentList('ReviseByMe', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_to_revise"));
		if($resArr) {
			$this->printList($resArr, $previewer, 'listRevisions');
		} else {
			printMLText("no_docs_to_revise");
		}
	} /* }}} */

	function listReceipts() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		$resArr = $dms->getDocumentList('ReceiptByMe', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_to_receipt"));
		if($resArr) {
			$this->printList($resArr, $previewer, 'listReceipts');
		} else {
			printMLText("no_docs_to_receipt");
		}

	} /* }}} */

	function listRejects() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of documents owned by current user that has
		 * been rejected.
		 */
		$resArr = $dms->getDocumentList('RejectOwner', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_user_rejected"));
		if ($resArr) {
			$this->printList($resArr, $previewer, 'listRejects');
		}
		else printMLText("no_docs_rejected");

	} /* }}} */

	function listLockedDocs() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of documents locked by current user */
		$resArr = $dms->getDocumentList('LockedByMe', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_locked_by_you"));
		if ($resArr) {
			$this->printList($resArr, $previewer, 'listLockedDocs');
		}
		else printMLText("no_docs_locked");

	} /* }}} */

	function listExpiredOwner() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of documents expired and owned by current user */
		$resArr = $dms->getDocumentList('ExpiredOwner', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_expired"));
		if ($resArr) {
			$this->printList($resArr, $previewer, 'listExpiredOwner');
		}
		else printMLText("no_docs_expired");

	} /* }}} */

	function listObsoleteOwner() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of obsolete documents and owned by current user */
		$resArr = $dms->getDocumentList('ObsoleteOwner', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_user_obsolete"));
		if ($resArr) {
			$this->printList($resArr, $previewer, 'listObsoleteOwner');
		}
		else printMLText("no_docs_obsolete");

	} /* }}} */

	function listNeedsCorrectionOwner() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of obsolete documents and owned by current user */
		$resArr = $dms->getDocumentList('NeedsCorrectionOwner', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_user_needs_correction"));
		if ($resArr) {
			$this->printList($resArr, $previewer, 'listNeedsCorrectionOwner');
		}
		else printMLText("no_docs_needs_correction");

	} /* }}} */

	function listDraftOwner() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of draft documents and owned by current user */
		$resArr = $dms->getDocumentList('DraftOwner', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_user_draft"));
		if ($resArr) {
			$this->printList($resArr, $previewer, 'listDraftOwner');
		}
		else printMLText("no_docs_draft");

	} /* }}} */

	function listCheckedoutDocs() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$cachedir = $this->params['cachedir'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		/* Get list of documents checked out by current user */
		$resArr = $dms->getDocumentList('CheckedOutByMe', $user, false, $orderby, $orderdir);
		if (is_bool($resArr) && !$resArr) {
			$this->contentHeading(getMLText("warning"));
			$this->contentContainer(getMLText("internal_error_exit"));
			$this->htmlEndPage();
			exit;
		}

		$this->contentHeading(getMLText("documents_checked_out_by_you"));
		if ($resArr) {
			$this->printList($resArr, $previewer, 'listCheckedoutDocs');
		}
		else printMLText("no_docs_checked_out");
	} /* }}} */

	function show() { /* {{{ */
		$dms = $this->params['dms'];
		$user = $this->params['user'];
		$orderby = $this->params['orderby'];
		$orderdir = $this->params['orderdir'];
		$listtype = $this->params['listtype'];
		$cachedir = $this->params['cachedir'];
		$workflowmode = $this->params['workflowmode'];
		$previewwidth = $this->params['previewWidthList'];
		$previewconverters = $this->params['previewConverters'];
		$timeout = $this->params['timeout'];
		$xsendfile = $this->params['xsendfile'];

		$db = $dms->getDB();
		$previewer = new SeedDMS_Preview_Previewer($cachedir, $previewwidth, $timeout, $xsendfile);
		$previewer->setConverters($previewconverters);

		$this->htmlAddHeader('<script type="text/javascript" src="../styles/'.$this->theme.'/bootbox/bootbox.min.js"></script>'."\n", 'js');

		$this->htmlStartPage(getMLText("my_documents"));
		$this->globalNavigation();
		$this->contentStart();
		$this->pageNavigation(getMLText("my_documents"), "my_documents");

		echo '<div class="row-fluid">';
		echo '<div class="span3">';
		$this->contentHeading(getMLText("my_documents"));
		echo '<ul class="nav nav-list bs-docs-sidenav _affix">';
		$resArr = $dms->getDocumentList('MyDocs', $user);
		echo '<li class=""><a data-href="#all_documents" data-action="listMyDocs"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("all_documents").'</a></li>';
		$resArr = $dms->getDocumentList('ReceiveOwner', $user);
		echo '<li class=""><a data-href="#documents_user_reception" data-action="listReceiveOwner"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_user_reception").'</a></li>';
		$resArr = $dms->getDocumentList('NoReceiveOwner', $user);
		echo '<li class=""><a data-href="#documents_user_no_reception" data-action="listNoReceiveOwner"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_user_no_reception").'</a></li>';
		if($workflowmode == 'traditional') {
			$resArr = $dms->getDocumentList('AppRevOwner', $user);
			echo '<li class=""><a data-href="#documents_user_requiring_attention" data-action="listDocsToLookAt"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_user_requiring_attention").'</a></li>';
		}
		echo '</ul>';
		$this->contentHeading(getMLText("documents_in_process"));
		echo '<ul class="nav nav-list bs-docs-sidenav _affix">';
		$resArr = $dms->getDocumentList('DraftOwner', $user);
		echo '<li class=""><a data-href="#documents_user_draft" data-action="listDraftOwner"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_user_draft").'</a></li>';
		if($workflowmode == 'traditional') {
			$resArr = $dms->getDocumentList('RejectOwner', $user);
			echo '<li class=""><a data-href="#documents_user_rejected" data-action="listRejects"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_user_rejected").'</a></li>';
		}
		$resArr = $dms->getDocumentList('CheckedOutByMe', $user);
		echo '<li class=""><a data-href="#documents_checked_out_by_you" data-action="listCheckedoutDocs"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_checked_out_by_you").'</a></li>';
		$resArr = $dms->getDocumentList('LockedByMe', $user);
		echo '<li class=""><a data-href="#documents_locked_by_you" data-action="listLockedDocs"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_locked_by_you").'</a></li>';
		echo '</ul>';
		$this->contentHeading(getMLText("tasks"));
		echo '<ul class="nav nav-list bs-docs-sidenav _affix">';
		if($workflowmode == 'traditional') {
			$resArr = $dms->getDocumentList('ReviewByMe', $user);
			echo '<li class=""><a data-href="#documents_to_review" data-action="listReviews"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_to_review").'</a></li>';
		}
		if($workflowmode == 'traditional' || $workflowmode == 'traditional_only_approval') {
			$resArr = $dms->getDocumentList('ApproveByMe', $user);
			echo '<li class=""><a data-href="#documents_to_approve" data-action="listApprovals"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_to_approve").'</a></li>';
		} else {
			$resArr = $dms->getDocumentList('WorkflowByMe', $user);
			echo '<li class=""><a data-href="#documents_to_process" data-action="listWorkflow"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_to_process").'</a></li>';
		}
		$resArr = $dms->getDocumentList('ReceiptByMe', $user);
		echo '<li class=""><a data-href="#documents_to_receipt" data-action="listReceipts"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_to_receipt").'</a></li>';
		$resArr = $dms->getDocumentList('ReviseByMe', $user);
		echo '<li class=""><a data-href="#documents_to_revise" data-action="listRevisions"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_to_revise").'</a></li>';
		$resArr = $dms->getDocumentList('NeedsCorrectionOwner', $user);
		echo '<li class=""><a data-href="#documents_user_needs_correction" data-action="listNeedsCorrectionOwner"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_user_needs_correction").'</a></li>';
		echo '</ul>';
		$this->contentHeading(getMLText("archive"));
		echo '<ul class="nav nav-list bs-docs-sidenav _affix">';
		$resArr = $dms->getDocumentList('ExpiredOwner', $user);
		echo '<li class=""><a data-href="#documents_user_expiration" data-action="listExpiredOwner"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_user_expiration").'</a></li>';
		$resArr = $dms->getDocumentList('ObsoleteOwner', $user);
		echo '<li class=""><a data-href="#documents_user_obsolete" data-action="listObsoleteOwner"><span class="badge '.($resArr ? 'badge-info ' : '').'badge-right">'.count($resArr).'</span>'.getMLText("documents_user_obsolete").'</a></li>';
		echo '</ul>';
		echo '</div>';
		echo '<div class="span9">';

		echo '<div id="kkkk" class="ajax" data-view="MyDocuments" data-action="'.($listtype ? $listtype : 'listDocsToLookAt').'"></div>';

		echo '</div>';
		echo '</div>';

		$this->contentEnd();
		$this->htmlEndPage();
	} /* }}} */
}
?>
