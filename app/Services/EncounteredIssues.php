<?php

namespace App\Services;

use App\Models\Support;
use Illuminate\Support\Collection;

class EncounteredIssues
{
    /** Public summaries use fixed topic names, never a requester's original description. */
    private const TOPICS = [
        'Need to create an email account' => '/(?:creat|setup|set up|set-up|new|register|activat|gawa).{0,45}(?:e-?mail|account)|(?:e-?mail|account).{0,35}(?:creat|register|activat)/iu',
        'Need to reset my password' => '/password|passwd|reset.{0,30}(?:outlook|account)|locked? (?:out|account)|(?:cannot|can.t|unable).{0,12}(?:log ?in|sign ?in)/iu',
        'Cannot send or receive emails' => '/outlook|e-?mail|mailbox|webmail/iu',
        'Computer has an invalid IP configuration' => '/\bip\b.{0,25}(?:config|invalid|address|conflict)|(?:invalid|valid).{0,15}\bip\b|\bdhcp\b|\bdns\b/iu',
        'Need help installing or organizing cables' => '/cabl(?:e|ing)|\blan\b.{0,20}(?:install|setup|set up)|patch (?:cord|panel)/iu',
        'Telephone is not connecting' => '/\b(?:phone|telephone|voip|intercom|pabx)\b/iu',
        'Internet connection keeps disconnecting' => '/(?:intermittent|unstable|disconnect|putol).{0,40}(?:internet|network|connection|wi[ -]?fi)|(?:internet|network|connection|wi[ -]?fi).{0,40}(?:intermittent|unstable|disconnect|putol)/iu',
        'Slow internet connection' => '/(?:slow|mabagal).{0,40}(?:internet|network|connection|wifi|wi-fi)|(?:internet|network|connection|wifi|wi-fi).{0,40}(?:slow|mabagal)/iu',
        'Cannot connect to Wi-Fi' => '/wi[ -]?fi|wireless/iu',
        'No internet connection' => '/internet|network|connectivity|\blan\b|router|modem/iu',
        'Need help setting up a projector' => '/projector|projection|projector screen/iu',
        'Paper is jammed in the printer' => '/paper.{0,15}(?:jam|feed)|(?:jam|feed).{0,15}paper/iu',
        'Printer needs ink or toner' => '/\b(?:ink|toner|cartridge)\b/iu',
        'Cannot scan a document' => '/scanner|scanning|\bscan\b/iu',
        'Cannot print a document' => '/print|photocop/iu',
        'Computer shows a blue screen' => '/blue[ -]?screen|\bbsod\b|system crash|stop code/iu',
        'Computer is slow or keeps freezing' => '/(?:slow|freez|hang|mabagal).{0,35}(?:computer|pc|laptop|desktop)|(?:computer|pc|laptop|desktop).{0,35}(?:slow|freez|hang|mabagal)/iu',
        'Computer will not turn on' => '/(?:no|won.t|not|cannot|can.t|unable|ayaw).{0,25}(?:power|boot|start|turn on|bukas)|(?:boot|startup|power).{0,20}(?:fail|error|issue|problem)/iu',
        'Need to install or update software' => '/install|software update|windows update|application update|reformat|formatting/iu',
        'Application is not working properly' => '/software|application|\bapp\b|license|licence|microsoft|excel|\bword\b/iu',
        'Monitor is not displaying properly' => '/monitor|display|black screen|screen flicker/iu',
        'Keyboard or mouse is not working' => '/keyboard|\bmouse\b|touchpad/iu',
        'Audio or video is not working in a meeting' => '/audio|speaker|microphone|camera|webcam|\bzoom\b|video conferenc/iu',
        'Need to recover or back up files' => '/recover|backup|back up|lost file|deleted file|data loss/iu',
        'Computer may have a virus' => '/virus|malware|ransomware|antivirus|phishing/iu',
        'Computer needs repair or maintenance' => '/repair|troubleshoot|maintenance|cleaning/iu',
    ];

    public function options($taskId, string $service): Collection
    {
        $catalog = json_decode(file_get_contents(resource_path('data/issue-options.json')), true, 512, JSON_THROW_ON_ERROR);
        $service = mb_strtolower($service);
        $group = preg_match('/microsoft|account|email|outlook/', $service) ? 'microsoft'
            : (preg_match('/internet|network|wi-fi/', $service) ? 'network'
            : (preg_match('/print|peripheral|scan/', $service) ? 'printer'
            : (preg_match('/software|application/', $service) ? 'software'
            : (preg_match('/computer|laptop|hardware|pc\//', $service) ? 'computer' : 'general'))));
        $descriptions = [];
        foreach ($catalog as $items) {
            foreach ($items as [$name, $description]) { $descriptions[$name] = $description; }
        }
        $issues = $this->forService($taskId)->keyBy('name');
        foreach ($catalog[$group] as [$name, $description]) {
            if ($issues->count() >= 9) break;
            if (! $issues->has($name)) $issues->put($name, (object) ['name' => $name]);
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('service_issues')) {
            foreach (\App\Models\ServiceIssue::where('task_id', $taskId)->orderBy('name')->get() as $custom) {
                if (! $issues->has($custom->name)) $issues->put($custom->name, (object) ['name' => $custom->name]);
            }
        }
        return $issues->values()->map(function ($issue) use ($descriptions) {
            $issue->description = $descriptions[$issue->name] ?? 'I need technical assistance with this concern: '.$issue->name.'. Please help me check and resolve the problem.';
            return $issue;
        });
    }

    public function forService($taskId, int $limit = 9): Collection
    {
        $counts = [];
        foreach (Support::where('task_id', $taskId)->whereNotNull('support_specifics')->cursor(['support_specifics']) as $ticket) {
            $description = mb_strtolower(trim(preg_replace('/\s+/u', ' ', strip_tags($ticket->support_specifics))));
            foreach (self::TOPICS as $name => $pattern) {
                if (preg_match($pattern, $description)) {
                    $counts[$name] = ($counts[$name] ?? 0) + 1;
                    break; // Each historical ticket contributes once to the ranked topics.
                }
            }
        }
        return collect($counts)->map(fn ($count, $name) => (object) ['name' => $name, 'reports' => $count])
            ->sort(fn ($left, $right) => ($right->reports <=> $left->reports) ?: strcmp($left->name, $right->name))
            ->take($limit)->values();
    }
}
