<?php

namespace App\Services;

use App\Models\Support;
use Illuminate\Support\Collection;

class EncounteredIssues
{
    /** Public summaries use fixed topic names, never a requester's original description. */
    private const TOPICS = [
        'Email / account creation' => '/(?:creat|setup|set up|set-up|new|register|activat|gawa).{0,45}(?:e-?mail|account)|(?:e-?mail|account).{0,35}(?:creat|register|activat)/iu',
        'Password reset / account access' => '/password|passwd|reset.{0,30}(?:outlook|account)|locked? (?:out|account)|(?:cannot|can.t|unable).{0,12}(?:log ?in|sign ?in)/iu',
        'Email / Outlook issues' => '/outlook|e-?mail|mailbox|webmail/iu',
        'Invalid IP / network configuration' => '/\bip\b.{0,25}(?:config|invalid|address|conflict)|(?:invalid|valid).{0,15}\bip\b|\bdhcp\b|\bdns\b/iu',
        'Cable installation / management' => '/cabl(?:e|ing)|\blan\b.{0,20}(?:install|setup|set up)|patch (?:cord|panel)/iu',
        'Phone / telephone connection' => '/\b(?:phone|telephone|voip|intercom|pabx)\b/iu',
        'Slow / intermittent internet' => '/(?:slow|intermittent|unstable|disconnect|mabagal|putol).{0,40}(?:internet|network|connection|wifi|wi-fi)|(?:internet|network|connection|wifi|wi-fi).{0,40}(?:slow|intermittent|unstable|disconnect|mabagal|putol)/iu',
        'Wi-Fi connection' => '/wi[ -]?fi|wireless/iu',
        'Internet / network connection' => '/internet|network|connectivity|\blan\b|router|modem/iu',
        'Projector / presentation setup' => '/projector|projection|projector screen/iu',
        'Printer paper jam / feeding' => '/paper.{0,15}(?:jam|feed)|(?:jam|feed).{0,15}paper/iu',
        'Printer ink / toner' => '/\b(?:ink|toner|cartridge)\b/iu',
        'Printer / scanner issues' => '/print|scanner|scanning|photocop/iu',
        'Blue screen / system crash' => '/blue[ -]?screen|\bbsod\b|system crash|stop code/iu',
        'Slow / freezing computer' => '/(?:slow|freez|hang|mabagal).{0,35}(?:computer|pc|laptop|desktop)|(?:computer|pc|laptop|desktop).{0,35}(?:slow|freez|hang|mabagal)/iu',
        'Computer startup / power' => '/(?:no|won.t|not|cannot|can.t|unable|ayaw).{0,25}(?:power|boot|start|turn on|bukas)|(?:boot|startup|power).{0,20}(?:fail|error|issue|problem)/iu',
        'Software installation / update' => '/install|software update|windows update|application update|reformat|formatting/iu',
        'Software / application error' => '/software|application|\bapp\b|license|licence|microsoft|excel|\bword\b/iu',
        'Display / monitor issues' => '/monitor|display|black screen|screen flicker/iu',
        'Keyboard / mouse issues' => '/keyboard|\bmouse\b|touchpad/iu',
        'Audio / video meeting issues' => '/audio|speaker|microphone|camera|webcam|\bzoom\b|video conferenc/iu',
        'File / data recovery' => '/recover|backup|back up|lost file|deleted file|data loss/iu',
        'Virus / malware concerns' => '/virus|malware|ransomware|antivirus|phishing/iu',
        'Computer repair / maintenance' => '/repair|troubleshoot|maintenance|cleaning/iu',
    ];

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
