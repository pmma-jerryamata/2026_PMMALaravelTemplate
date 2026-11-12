<?php

namespace App\Services;

use App\Models\ServiceCategory;

class RequestIssues
{
    public function options(string $category): array
    {
        $title = strtolower(ServiceCategory::label($category));
        $group = preg_match('/microsoft|account|email|outlook/', $title) ? 'microsoft'
            : (preg_match('/internet|network|wi-fi/', $title) ? 'network'
            : (preg_match('/print|peripheral|scan/', $title) ? 'printer'
            : (preg_match('/software|application/', $title) ? 'software'
            : (preg_match('/computer|laptop|hardware|pc\//', $title) ? 'computer' : 'general'))));
        return array_slice(json_decode(file_get_contents(resource_path('issue-options.json')), true, 512, JSON_THROW_ON_ERROR)[$group], 0, 9);
    }
}
