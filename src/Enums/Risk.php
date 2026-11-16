<?php

namespace JeffersonGoncalves\LgpdIncidents\Enums;

/**
 * Res. CD/ANPD 15/2024, art. 5: an incident must be communicated when it may cause relevant risk or damage
 * to the data subjects (sensitive data, children, financial data, authentication data, large scale...).
 */
enum Risk: string
{
    case Low = 'low';
    case Relevant = 'relevant';
}
