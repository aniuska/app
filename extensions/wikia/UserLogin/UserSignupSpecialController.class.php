<?php

/**
 * UserSignup Special Page
 * @author Hyun
 * @author Saipetch
 *
 */
class UserSignupSpecialController extends WikiaSpecialPageController {

	private $userLoginHelper = null;

	public function __construct() {
		parent::__construct('UserSignup', '', false);
	}

	public function init() {
		$this->isMonobook = ($this->wg->User->getSkin() instanceof SkinMonobook);
		$this->isEn = ($this->wg->Lang->getCode() == 'en') ? true : false;
		$this->userLoginHelper = F::build( 'UserLoginHelper' );
	}

	/**
	 * @brief serves standalone signup page on GET.  if POSTed, parameters will be required.
	 * @details
	 *   on GET, template will render
	 *   on POST,
	 *     if signup is successful, it will redirect to returnto, or mainpage of wiki
	 *     if signup is not successful, the template will render error messages, highlighting the errors
	 * @requestParam string username - on POST
	 * @requestParam string email - on POST
	 * @requestParam string password - on POST
	 * @requestParam string birthmonth - on POST
	 * @requestParam string birthday - on POST
	 * @requestParam string birthyear - on POST
	 * @requestParam string captcha - on POST
	 * @requestParam string returnto - url to return to upon successful login
	 * @requestParam string signupToken
	 * @requestParam string uselang
	 * @responseParam string result [ok/error]
	 * @responseParam string msg - result message
	 * @responseParam string errParam - error param
	 */
	public function index() {
		$this->wg->Out->setPageTitle($this->wf->Msg('usersignup-page-title'));
		$this->response->addAsset('extensions/wikia/UserLogin/css/UserSignup.scss');

		if ( F::app()->checkSkin( 'oasis' )) {
			$this->response->addAsset('extensions/wikia/UserLogin/js/UserSignup.js');
		}

		if ( !empty($this->wg->EnableFacebookConnectExt) ) {
			$this->response->addAsset('extensions/wikia/UserLogin/js/UserLoginFacebookPageInit.js');
		}

		// hide things in the skin
		$this->wg->SuppressWikiHeader = true;
		$this->wg->SuppressPageHeader = true;
		$this->wg->SuppressFooter = true;
		$this->wg->SuppressAds = true;
		$this->wg->SuppressToolbar = true;

		// form params
		$this->username = $this->request->getVal( 'username', '' );
		$this->email = $this->request->getVal( 'email', '' );
		$this->password = $this->request->getVal( 'password', '' );
		$this->birthmonth = $this->request->getVal( 'birthmonth', '' );
		$this->birthday = $this->request->getVal( 'birthday', '' );
		$this->birthyear = $this->request->getVal( 'birthyear', '' );
		$this->returnto = $this->request->getVal( 'returnto', '' );
		$this->byemail = $this->request->getBool( 'byemail', false );
		$this->signupToken = UserLoginHelper::getSignupToken();
		$this->uselang = $this->request->getVal( 'uselang', 'en' );

		$this->avatars = $this->userLoginHelper->getRandomAvatars();
		$this->popularWikis = $this->userLoginHelper->getRandomWikis();

		// template params
		$this->pageHeading = wfMsg('usersignup-heading');
		$this->createAccountButtonLabel = wfMsg('createaccount');
		if($this->byemail) {
			$this->pageHeading = wfMsg('usersignup-heading-byemail');
			$this->createAccountButtonLabel = wfMsg('usersignup-createaccount-byemail');
		}

		// process signup
		$redirected = $this->request->getVal('redirected', '');
		if ($this->wg->Request->wasPosted() && empty($redirected)) {

			$response = $this->app->sendRequest( 'UserSignupSpecial', 'signup' );

			$this->result = $response->getVal( 'result', '' );
			$this->msg = $response->getVal( 'msg', '' );
			$this->errParam = $response->getVal( 'errParam', '' );

			if ( $this->result == 'ok' ) {
				$params = array(
					'method' => 'sendConfirmationEmail',
					'username' => $this->username,
					'byemail' => intval($this->byemail),
					'uselang' => $this->uselang,
				);
				$redirectUrl = $this->wg->title->getFullUrl( $params );
				$this->wg->out->redirect( $redirectUrl );
			}

		}
	}

	public function captcha() {
		$this->rawHtml = '';
		$captchaObj = self::getCaptchaObj();
		if(!empty($captchaObj)) {
			$this->rawHtml = $captchaObj->getForm();
			$this->isFancyCaptcha = (class_exists('FancyCaptcha') && $captchaObj instanceof FancyCaptcha);
		}
	}

	private function getCaptchaObj() {
		$captchaObj = null;
		if(empty( $this->wg->WikiaEnableConfirmEditExt ) && !empty( $this->wg->EnableReCaptchaExt) ) {
			$captchaObj = new ReCaptcha();
		} else if(!empty($this->wg->WikiaEnableConfirmEditExt)) {
			$captchaObj = ConfirmEditHooks::getInstance();
		}

		return $captchaObj;
	}

	/**
	 * @brief ajax call for signup.  returns status code
	 * @details
	 *   for use with ajax call or standalone data call only
	 * @requestParam string username
	 * @requestParam string email
	 * @requestParam string password
	 * @requestParam string birthmonth
	 * @requestParam string birthday
	 * @requestParam string birthyear
	 * @requestParam string captcha
	 * @responseParam string result [ok/error]
	 * @responseParam string msg - result message
	 * @responseParam string errParam - error param
	 */
	public function signup() {
		// Init session if necessary
		if ( session_id() == '' ) {
			$this->wf->SetupSession();
		}

		$signupForm = F::build( 'UserLoginForm', array(&$this->wg->request, 'signup') );

		$byemail = $this->wg->request->getBool( 'byemail', false );
		if ( $byemail ) {
			$ret = $signupForm->addNewAccountMailPassword();
		} else {
			$ret = $signupForm->addNewAccount();
		}

		$this->result = ( $signupForm->msgType == 'error' ) ? $signupForm->msgType : 'ok' ;
		$this->msg = $signupForm->msg;
		$this->errParam = $signupForm->errParam;

		// pass and ID of created account for FBConnect feature
		if ($ret instanceof User) {
			$this->userId = $ret->getId();
			$this->userPage = $ret->getUserPage()->getFullUrl();
		}
	}

	/**
	 * @brief renders content in modal dialog
	 * @details
	 * @requestParam string username
	 */
	public function getEmailConfirmationMarketingModal() {
		// TODO: need spam protection here HWL 2011-12-22
		$response = $this->userLoginHelper->sendConfirmationEmail( $this->request->getVal('username', '') );
		$this->result = $response['result'];
		$this->msg = $response['msg'];
	}

	/**
	 * @brief send confirmation email
	 * @resquestParam string username
	 * @resquestParam boolean byemail
	 * @responseParam string result [ok/error/invalidsession/confirmed]
	 * @responseParam string msg - result message
	 * @responseParam string msgEmail
	 * @responseParam string errParam
	 * @responseParam string heading
	 * @responseParam string subheading
	 */
	public function sendConfirmationEmail() {
		if($this->request->getVal('format', '') !== 'json') {
			$this->wg->Out->setPageTitle($this->wf->Msg('usersignup-confirm-page-title'));
			$this->response->addAsset('skins/oasis/css/modules/WikiaTooltip.scss');
			$this->response->addAsset('extensions/wikia/UserLogin/css/UserSignup.scss');
			$this->response->addAsset('extensions/wikia/UserLogin/css/ConfirmEmail.scss');
			$this->response->addAsset('skins/common/jquery/jquery.wikia.tooltip.js');
			$this->response->addAsset('extensions/wikia/UserLogin/js/ConfirmEmail.js');

			// hide things in the skin
			$this->wg->SuppressWikiHeader = true;
			$this->wg->SuppressPageHeader = true;
			$this->wg->SuppressFooter = true;
			$this->wg->SuppressAds = true;
			$this->wg->SuppressToolbar = true;
		}

		$this->username = $this->request->getVal('username', '');
		$this->byemail = $this->request->getBool('byemail', false);

		// default heading, subheading, msg
		// depending on what happens, default will be over written below
		$mailTo = $this->username;
		$tempUser = F::build( 'TempUser', array( $this->username ), 'getTempUserFromName' );
		if ( $tempUser ) {
			if ( (isset($_SESSION['tempUserId']) && $_SESSION['tempUserId'] == $tempUser->getId()) ) {
				$mailTo = $tempUser->getEmail();
			}
		}

		$this->result = 'ok';
		$mailTo = htmlspecialchars($mailTo);

		$this->heading = $this->wf->Msg( 'usersignup-confirmation-heading' );
		$this->subheading = $this->wf->Msg( 'usersignup-confirmation-subheading' );
		$this->msg = $this->wf->MsgExt( 'usersignup-confirmation-email-sent', array('parseinline'), $mailTo );
		$this->msgEmail = '';
		$this->errParam = '';

		if ($this->wg->Request->wasPosted()) {
			$action = $this->request->getVal('action','');
			if ( $action=='resendconfirmation' ) {
				$response = $this->userLoginHelper->sendConfirmationEmail( $this->username );
				$this->result = $response['result'];
				$this->msg = $response['msg'];
				$this->heading = $this->wf->Msg('usersignup-confirmation-heading-email-resent');
			} else if ( $action == 'changeemail' ) {
				$this->email = $this->request->getVal('email', '');
				$params = array(
					'username' => $this->username,
					'email' => $this->email
				);
				$response = $this->sendSelfRequest( 'changeTempUserEmail', $params );

				$this->result = $response->getVal( 'result','' );

				if($this->result == 'ok') {
					$this->msg = $response->getVal( 'msg','' );
					$this->heading = $this->wf->Msg('usersignup-confirmation-heading-email-resent');
				} else if($this->result == 'error') {
					$this->msgEmail = $response->getVal( 'msg','' );
					$this->errParam = $response->getVal( 'errParam', '');
				} else if ( $this->result == 'confirmed' ) {
					$this->heading = $this->wf->Msg( 'usersignup-confirm-page-heading-confirmed-user' );
					$this->subheading = $this->wf->Msg( 'usersignup-confirm-page-subheading-confirmed-user' );					
					$this->msg = $response->getVal( 'msg','' );
				}
			}

			// redirect to login page if invalid session
			if ( $this->result == 'invalidsession' ) {
				$titleObj = SpecialPage::getTitleFor( 'Userlogin' );
				$this->wg->out->redirect( $titleObj->getFullURL() );
				return;
			}
		} else {
			if ( $this->byemail == true ) {
				$this->heading = $this->wf->Msg( 'usersignup-account-creation-heading' );
				$this->subheading = $this->wf->Msg( 'usersignup-account-creation-subheading', $mailTo );
				$this->msg = $this->wf->MsgExt( 'usersignup-account-creation-email-sent', array('parseinline'), $mailTo, $this->username );
			}
		}
	}

	/**
	 * change temp user's email and send reconfirmation email
	 * @requestParam string username
	 * @requestParam string email
	 * @responseParam string result [ok/error/invalidsession/confirmed]
	 * @responseParam string msg - result messages
	 * @responseParam string errParam - error param
	 */
	public function changeTempUserEmail() {
		$email = $this->request->getVal( 'email', '' );
		if ( empty($email) ) {
			$this->result = 'error';
			$this->msg = $this->wf->Msg( 'usersignup-error-empty-email' );
			$this->errParam = 'email';
			return;
		}

		if ( !User::isValidEmailAddr( $email ) ) {
			$this->result = 'error';
			$this->msg = $this->wf->Msg( 'usersignup-error-invalid-email' );
			$this->errParam = 'email';
			return;
		}

		$username = $this->request->getVal('username');
		if ( empty($username) ) {
			$this->result = 'error';
			$this->msg = $this->wf->Msg( 'userlogin-error-noname' );
			$this->errParam = 'username';
			return;
		}

		$tempUser = F::build( 'TempUser', array( $username ), 'getTempUserFromName' );
		if ( $tempUser == false ) {
			$user = F::build( 'User', array( $username ), 'newFromName' );
			if ( $user instanceof User && $user->getID() != 0 ) {
				$this->result = 'confirmed';
				$this->msg = $this->wf->MsgExt( 'usersignup-error-confirmed-user', array('parseinline'), $username, $user->getUserPage()->getFullURL() );
			} else {
				$this->result = 'error';
				$this->msg = $this->wf->Msg( 'userlogin-error-nosuchuser' );
			}
			$this->errParam = 'username';
			return;
		}

		if ( !(isset($_SESSION['tempUserId']) && $_SESSION['tempUserId'] == $tempUser->getId()) ) {
			$this->result = 'invalidsession';
			$this->msg = $this->wf->Msg( 'usersignup-error-invalid-user' );
			$this->errParam = 'username';
			return;
		}

		$memKey = $this->wf->SharedMemcKey( 'wikialogin', 'email_changes', $tempUser->getId() );
		$emailChanges = intval( $this->wg->Memc->get($memKey) );
		if ( $emailChanges >= UserLoginHelper::LIMIT_EMAIL_CHANGES ) {
			$this->result = 'error';
			$this->msg = $this->wf->Msg( 'usersignup-error-too-many-changes' );
			$this->errParam = 'email';
			return;
		}

		// increase counter for email changes
		$this->userLoginHelper->incrMemc( $memKey );

		$this->result = 'ok';
		$this->msg = $this->wf->Msg( 'usersignup-reconfirmation-email-sent', htmlspecialchars($email) );
		if ( $email != $tempUser->getEmail() ) {
			$tempUser->setEmail( $email );
			$tempUser->updateData();

			// send reconfirmation email
			$user = $tempUser->mapTempUserToUser();
			$result = $user->sendReConfirmationMail();
			$tempUser->saveSettingsTempUserToUser( $user );

			// set counter to 1 for confirmation emails sent
			$memKey = $this->userLoginHelper->getMemKeyConfirmationEmailsSent( $tempUser->getId() );
			$this->wg->Memc->set( $memKey, 1, 24*60*60 );

			if( WikiError::isError( $result ) ) {
				$this->result = 'error';
				$this->msg = $this->wf->Msg( 'userlogin-error-mail-error', $result->getMessage() );
			}
		}
	}

	/**
	 * validate form
	 * @requestParam string field [username/password/email/birthdate]
	 * @requestParam string username
	 * @requestParam string email
	 * @requestParam string password
	 * @requestParam string birthmonth
	 * @requestParam string birthday
	 * @requestParam string birthyear
	 * @responseParam string result [ok/error]
	 * @responseParam string msg - result message
	 * @responseParam string errParam - error param
	 */
	public function formValidation() {
		$field = $this->request->getVal( 'field', '' );

		$signupForm = F::build( 'UserLoginForm', array(&$this->wg->request, 'signup') );

		switch( $field ) {
			case 'username' :
				$response = $signupForm->initValidationUsername();
				if ( $signupForm->msgType != 'error' ) {
					$result = $this->wf->ValidateUserName( $signupForm->mName );

					if ( $result === true ) {
						$msgKey = '';
						if ( !$this->wf->RunHooks('cxValidateUserName', array($signupForm->mName, &$msgKey)) ) {
							$result = $msgKey;
						}
					}

					if ( $result !== true ) {
						$msg = '';
						if ( $result == 'userlogin-bad-username-taken' ) {
							$msg = $this->wf->Msg('userlogin-error-userexists');
						} else if ( $result == 'userlogin-bad-username-character' ) {
							$msg = $this->wf->Msg('usersignup-error-symbols-in-username');
						} else if ( $result == 'userlogin-bad-username-length' ) {
							$msg = $this->wf->Msg('usersignup-error-username-length', $this->wg->WikiaMaxNameChars);
						}

						$signupForm->msgType = 'error';
						$signupForm->msg = empty($msg) ? $result : $msg;
						$signupForm->errParam = 'username';
					}
				}
				break;
			case 'password' :
				$response = $signupForm->initValidationPassword();
				break;
			case 'email' :
				$response = $signupForm->initValidationEmail();
				break;
			case 'birthdate' :
				$response = $signupForm->initValidationBirthdate();
				break;
		}

		$this->result = ( $signupForm->msgType == 'error' ) ? $signupForm->msgType : 'ok' ;
		$this->msg = $signupForm->msg;
		$this->errParam = $signupForm->errParam;
	}

}
