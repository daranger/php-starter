<?php

namespace App\Admin\UI;

class FormBuilder
{
    public static function renderFields(object $cols, ?object $row): void
    {
        $checkboxFields = [
            'explicit',
            'noindex',
        ];
        $sliderFields = [
            'faq',
            'have_similar',
            'have_albums',
        ];
        $cols->data_seek(0);
        while ($c = $cols->fetchObject()) {
            if (in_array($f = $c->Field, ['id', 'password_hash', 'password'], true)) continue;
            $t = strtolower($c->Type);
            $v = htmlspecialchars($row->$f ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $len = preg_match('/\((\d+)/', $c->Type, $m) ? "maxlength='{$m[1]}'" : '';

            echo "<div class='form-grid'><label class='dashboard' for='{$f}'><b>{$f}</b><span>{$c->Type}</span></label>";
            if (str_contains($t, 'text') || str_contains($t, 'varchar(511)')) {
                echo "<textarea class='edit-textarea' id='{$f}' name='{$f}' {$len}>{$v}</textarea>";
            } elseif (str_contains($t, 'int') && preg_match('/date|time|created|updated/', $f)) {
                echo "<input id='{$f}' name='{$f}' type='datetime-local' value='" . (((int)($row->$f ?? 0) > 0) ? date('Y-m-d\TH:i', (int)$row->$f) : '') . "'>";
            } elseif ($t === 'tinyint(1)') {
                echo "<select id='{$f}' name='{$f}'>
                    <option value='0' " . (!$v ? 'selected' : '') . ">No</option>
                    <option value='1' " . ($v ? 'selected' : '') . ">Yes</option>
                  </select>";
            }elseif (in_array($f, $checkboxFields, true)) {

                    echo "
                        <input type='hidden' name='{$f}' value='0'>
                        <input class='custom-checkbox' type='checkbox' id='{$f}' name='{$f}' value='1' ".($v ? 'checked' : '').">
                    ";

            } elseif (in_array($f, $sliderFields, true)) {

                echo "<input type='hidden' name='{$f}' value='0'>
                <label class='switch'>
                    <input type='checkbox' name='{$f}' value='1' ".($v ? 'checked' : '').">
                    <span class='slider'></span>
                </label>";

            } elseif (str_starts_with($t, 'enum(')) {

                preg_match_all("/'([^']+)'/", $c->Type, $matches);

                $current = $row->$f ?? $c->Default ?? '';

                echo "<select id='{$f}' name='{$f}'>";

                foreach ($matches[1] as $option) {

                    $selected = ($current === $option) ? 'selected' : '';

                    echo "<option value='{$option}' {$selected}>{$option}</option>";
                }

                echo "</select>";
            } else {
                echo "<input id='{$f}' name='{$f}' type='" . (str_contains($t, 'int') ? 'number' : 'text') . "' value='{$v}' {$len}>";
            }
            echo "</div>";
        }
    }
}