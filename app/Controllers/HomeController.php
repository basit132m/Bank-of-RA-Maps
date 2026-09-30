<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Map;

final class HomeController extends Controller
{
    public function index(): string
    {
        return $this->view('home', [
            'title'       => null, // home uses the site name alone
            'description' => 'Original, hand-designed multiplayer maps for Command & Conquer: '
                . "Red Alert 2 — Yuri's Revenge. Free downloads, install guides and a community of RA2 players.",
            'featured'    => Map::featured(),
            'latest'      => Map::latest(6),
            'mapCount'    => Map::publishedCount(),
            'downloads'   => Map::totalDownloads(),
        ]);
    }
}
