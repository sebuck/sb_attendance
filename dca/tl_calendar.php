<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package   Attendance
 * @author    Sebastian Buck
 * @license   LGPL
 * @copyright Sebastian Buck 2014
 */

/*
 * Erweitert das System um einen ondelete_callback: Ruft beim Löschen eines Kalenders die 
 * Funktion al_deleteCalendar (siehe unten) auf.
 *
 */
array_insert($GLOBALS['TL_DCA']['tl_calendar']['config']['ondelete_callback'], -1, array
    (
        array('tl_attendanceCalendar', 'al_deleteCalendar')
    )
);

/**
 * Class tl_attendanceCalendar
 *
 * Zusätzliche Methode, um beim Löschen eines Kalenders, die enthaltenen Events aus der Anwesenheit zu löschen
 *
 * @copyright  Sebastian Buck 2014
 * @author     Sebastian Buck
 * @package    Attendance
 */
class tl_attendanceCalendar extends tl_calendar 
{
    /**
     * Call the "al_deleteCalendar" callback
     *
     * Diese Funktion wird beim Löschen eines Kalenders gerufen und löscht die zugehörigen Events aus tl_attendance 
     */
    public function al_deleteCalendar($dc) 
    {
        if ($dc instanceof \DataContainer && $dc->activeRecord) 
        {
            \sb_attendanceModel::deleteCalEvents($dc->activeRecord->id);
        }
    }
}