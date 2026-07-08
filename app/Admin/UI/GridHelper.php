<?php
namespace App\Admin\UI;

class GridHelper {
    public static function renderSortableTh(string $f, string $curS, string $curO): void {
        $isAct = $curS === $f;
        $next = $isAct && $curO === 'asc' ? 'desc' : 'asc';
        echo "<th " . ($isAct ? "class='sort-active'" : '') . ">
        <a href='?sort=" . urlencode($f) . "&order={$next}" . (isset($_GET['page']) ? '&page=' . (int)$_GET['page'] : '') . "' class='sort-link'>" . htmlspecialchars($f) . "<span>" . ($isAct ? ($curO === 'asc' ? ' ▲' : ' ▼') : '') . "</span></a>
        </th>";
    }

    public static function renderCell(string $k, ?string $v): void {
        if ($k === 'page_id' && !empty($v)) {
            echo "<td id='{$k}' class='truncate-cell'>";
            echo "<a href='/admin/redirect.php?id=" . (int)$v . "' 
                 target='_blank' 
                 class='page-link-badge' 
                 title='Visit page'
                 onclick='event.stopPropagation();'> {$v} ↗</a>";
            echo "</td>";
            return;
        }
        if (($k === 'last_login') && !empty($v)) {
            $color = time() - $v > 604800 ? '#dc3545' : (time() - $v > 259200 ? '#fd7e14' : '#067452');
            echo "<td style='color: {$color};'>" . date('d M Y, H:i', $v) . "</td>";
            return;
        }
        if ($k === 'created_at' && !empty($v)) {
            echo "<td style='color: #067452;'>" .date("d M Y, H:i",strtotime($v))."</td>";
            return;
        }
        if (($k === 'date' or $k === 'last_used') && !empty($v)) {
            echo "<td style='color: #067452;'>" .date("d M Y, H:i",$v)."</td>";
            return;
        }
        if ($k === 'rating_as_review' && is_numeric($v)) {
            $score = (int)$v;
            $stars = str_repeat('★', $score) . str_repeat('☆', 5 - $score);
            echo "<td id='{$k}' class='truncate-cell'><span class='rating-badge' title='Rating: {$score}/5'>{$stars}</span></td>";
            return;
        }
        $cl = strtolower(trim($v ?? ''));
        $b = match($cl) { '1', 'active', 'yes', 'true', 'like' => 'success', '0', 'inactive', 'no', 'false', 'dislike' => 'danger', 'pending', 'moderation', 'wait' => 'warning', default => (is_numeric($cl) && (int)$cl < 0 ? 'danger' : '') };
        echo "<td id='" . htmlspecialchars($k) . "' class='truncate-cell'>" . ($b ? "<span class='badge badge-{$b}'>" . htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</span>" : htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) . "</td>";
    }

}