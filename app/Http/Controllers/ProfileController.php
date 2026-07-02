<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\UserRepository;

class ProfileController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly \RobThree\Auth\TwoFactorAuth $tfa
    ) {}

    public function index(Request $request): Response
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return Response::redirect('/');
        }

        $user = $this->userRepository->find($userId);
        
        $tfaEnabled = !empty($user->two_factor_secret);
        
        // Генерируем секрет для настройки 2FA, если он не включен
        $tfaSetupSecret = null;
        $tfaSetupQr = null;
        
        if (!$tfaEnabled) {
            $tfaSetupSecret = $this->tfa->createSecret();
            Session::set('pending_2fa_secret', $tfaSetupSecret);
            $qrText = $this->tfa->getQRText($user->email, $tfaSetupSecret);
            $options = new \chillerlan\QRCode\QROptions([
                'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
                'scale' => 5,
            ]);
            $qr = new \chillerlan\QRCode\QRCode($options);
            $tfaSetupQr = $qr->render($qrText);
        }

        return new Response(view('profile', [
            'title' => __('nav_profile'),
            'user' => $user,
            'tfaEnabled' => $tfaEnabled,
            'tfaSetupSecret' => $tfaSetupSecret,
            'tfaSetupQr' => $tfaSetupQr
        ]));
    }
}
