<?php

namespace PhpSamurai\LaravelModuleMaker\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class NavigationManager
{
    /**
     * Add navigation link to layout.
     */
    public function addNavigationLink(string $moduleName): void
    {
        $layoutPath = resource_path('views/components/layouts/app.blade.php');

        if (!File::exists($layoutPath)) {
            return;
        }

        $content = File::get($layoutPath);
        $moduleRoute = Str::plural(strtolower($moduleName));
        $moduleLabel = Str::plural($moduleName);

        // Check if link already exists
        if (strpos($content, "href=\"/{$moduleRoute}\"") !== false) {
            return;
        }

        // Find the navigation section and add the link
        $navLink = "\n                            <a href=\"/{$moduleRoute}\" class=\"inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300\">\n                                {$moduleLabel}\n                            </a>";

        // Insert before the closing div of navigation links
        $pattern = '/(<div class="hidden space-x-8[^>]*>.*?)(\n\s*<\/div>)/s';

        if (preg_match($pattern, $content, $matches)) {
            $replacement = $matches[1] . $navLink . $matches[2];
            $content = preg_replace($pattern, $replacement, $content, 1);
            File::put($layoutPath, $content);
        }
    }

    /**
     * Remove navigation link from layout.
     */
    public function removeNavigationLink(string $moduleName): void
    {
        $layoutPath = resource_path('views/components/layouts/app.blade.php');

        if (!File::exists($layoutPath)) {
            return;
        }

        $content = File::get($layoutPath);
        $moduleRoute = Str::plural(strtolower($moduleName));

        // Remove the navigation link
        $pattern = '/\n\s*<a href="\/' . preg_quote($moduleRoute, '/') . '"[^>]*>.*?<\/a>/s';
        $content = preg_replace($pattern, '', $content);

        File::put($layoutPath, $content);
    }

    /**
     * Get all navigation links.
     */
    public function getNavigationLinks(): array
    {
        $layoutPath = resource_path('views/components/layouts/app.blade.php');

        if (!File::exists($layoutPath)) {
            return [];
        }

        $content = File::get($layoutPath);
        $links = [];

        // Extract all navigation links
        preg_match_all('/<a href="\/([^"]+)"[^>]*>\s*([^<]+)\s*<\/a>/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            if ($match[1] !== '' && $match[1] !== '/') {
                $links[] = [
                    'route' => $match[1],
                    'label' => trim($match[2]),
                ];
            }
        }

        return $links;
    }
}
