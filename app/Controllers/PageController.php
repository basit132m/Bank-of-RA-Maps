<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Models\Map;

/** Static informational pages. */
final class PageController extends Controller
{
    public function about(): string
    {
        return $this->view('pages/about', [
            'title'       => 'About',
            'description' => 'Who makes the maps at Bank of YR Maps, and why the site exists.',
            'mapCount'    => Map::publishedCount(),
        ]);
    }

    public function community(): string
    {
        return $this->view('pages/community', [
            'title'         => 'Community',
            'description'   => 'Join the Bank of YR Maps community of Red Alert 2 and '
                . "Yuri's Revenge players. Discord, map feedback and house rules.",
            'discordInvite' => (string) Config::get('community.discord_invite', ''),
            'contactEmail'  => (string) Config::get('community.contact_email', ''),
        ]);
    }

    public function guides(): string
    {
        return $this->view('guides/index', [
            'title'       => 'Guides',
            'description' => "Guides for installing Yuri's Revenge maps, playing online with "
                . 'CnCNet, and designing your own maps.',
        ]);
    }

    public function installGuide(): string
    {
        return $this->view('guides/install', [
            'title'       => "How to install Yuri's Revenge maps",
            'description' => "Step-by-step instructions for installing custom maps in Red Alert 2: "
                . "Yuri's Revenge — for CnCNet, the Origin and Steam releases, and the original CD version.",
        ]);
    }
}
