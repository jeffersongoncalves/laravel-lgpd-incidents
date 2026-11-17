<?php

return [

    'table' => 'lgpd_incidents',

    /*
     * Business days to notify the ANPD and the data subjects, counted from when the
     * controller learned the incident affected personal data (Res. CD/ANPD 15/2024, art. 6 and 9).
     * Small processing agents get double (Res. CD/ANPD 2/2022, art. 14): set 6.
     * Holidays come from jeffersongoncalves/laravel-brazil-holidays (add state/city ones there).
     */
    'deadline_business_days' => 3,

    /*
     * A partial notice to the ANPD must be complemented within 20 business days of it
     * (Res. CD/ANPD 15/2024, art. 6, §3). Pass partial: true to notifyAnpd() to track it.
     */
    'complement_business_days' => 20,

    /*
     * The incident register must be kept for at least 5 years (Res. 15/2024, art. 10):
     * younger records cannot be deleted.
     */
    'keep_years' => 5,

];
