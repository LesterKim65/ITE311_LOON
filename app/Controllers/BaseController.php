<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
	/**
	 * @var CLIRequest|IncomingRequest
	 */
	protected $request;

	/**
	 * @var list<string>
	 */
	protected $helpers = [];

	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);

		// Check if user is logged in and verify their status
		if (session()->get('isLoggedIn')) {
			// Verify user is still active in database
			$userModel = new \App\Models\UserModel();
			$userId = session()->get('id');
			$user = $userModel->find($userId);
			
			// If user not found or is inactive, destroy session
			if (!$user || (isset($user['status']) && $user['status'] === 'inactive')) {
				session()->destroy();
				// Store flag to redirect in the controller method itself
				$this->data['userInactive'] = true;
			} else {
				// Update session status if it changed
				if (isset($user['status'])) {
					session()->set('status', $user['status']);
				}
			}
		}

		// Load unread notification count for logged-in user
		try {
			if (session()->has('id')) {
				$notificationModel = new \App\Models\NotificationModel();
				$unreadCount = $notificationModel->getUnreadCount(session('id'));
				$this->data['unreadCount'] = $unreadCount;
			} else {
				$this->data['unreadCount'] = 0;
			}
		} catch (\Exception $e) {
			$this->data['unreadCount'] = 0;
		}
	}
}
