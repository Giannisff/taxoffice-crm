<?php

function dbDateFromGreek($dateStr) {
    // από ηη/μμ/εεεε σε εεεε-μμ-ηη
    if (!$dateStr) return null;
    [$d, $m, $y] = explode('/', $dateStr);
    return "$y-$m-$d";
}

function greekDateFromDb($dateStr) {
    if (!$dateStr || $dateStr == '0000-00-00') return '';
    [$y, $m, $d] = explode('-', $dateStr);
    return "$d/$m/$y";
}

function formatMoney($amount) {
    return number_format((float)$amount, 2, ',', '.') . ' €';
}
