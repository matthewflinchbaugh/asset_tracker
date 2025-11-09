<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetTagController extends Controller
{
    /**
     * Display a list of assets to generate tags for.
     */
    public function index()
    {
        $assets = Asset::where('status', 'active')
                       ->orderBy('name')
                       ->get();
                       
        return view('admin.tags.index', compact('assets'));
    }

    /**
     * Generate and display a single asset tag as an SVG.
     */
    public function show(Asset $asset)
    {
        $companyName = "Printed Solid Inc.";
        $assetName = htmlspecialchars($asset->name);
        $assetId = htmlspecialchars($asset->asset_tag_id);
        
        // Generate the full URL to the asset's show page
        $assetUrl = route('assets.show', $asset->id);
        
        // Use the external QR code generator API
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($assetUrl);
        
        // --- THIS IS THE FIX ---
        // We must escape the '&' for the XML/SVG parser.
        $qrUrl = str_replace('&', '&amp;', $qrUrl);
        // --- END FIX ---

        // Define the SVG content as a string.
        // This is a 3" x 1.5" label at 100dpi = 300x150px
        $svgContent = <<<SVG
<svg width="300px" height="150px" viewBox="0 0 300 150" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <style>
        .title { font-family: sans-serif; font-size: 16px; font-weight: bold; }
        .name { font-family: sans-serif; font-size: 14px; }
        .id { font-family: sans-serif; font-size: 20px; font-weight: bold; fill: #333; }
    </style>
    
    <!-- White background and black border -->
    <rect width="100%" height="100%" fill="white" stroke="black" stroke-width="1"/>
    
    <!-- QR Code on the right -->
    <image x="190" y="25" width="100" height="100" href="{$qrUrl}"/>
    
    <!-- Text Content on the left -->
    <text x="10" y="30" class="title">{$companyName}</text>
    
    <text x="10" y="70" class="name">{$assetName}</text>
    
    <text x="10" y="110" class="id">{$assetId}</text>
</svg>
SVG;

        // Return the raw SVG as an image
        return response($svgContent, 200)
                  ->header('Content-Type', 'image/svg+xml');
    }
}
