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
 * Erweitert das System um einen ondelete_callback: Ruft beim Löschen eines Termins die 
 * Funktion al_deleteEvent (siehe unten) auf.
 *
 */
array_insert($GLOBALS['TL_DCA']['tl_calendar_events']['config']['ondelete_callback'], -1, array
    (
        array('tl_attendanceEvents', 'al_deleteEvent')
    )
);

/*
 * Erweitert das System um einen onsubmit_callback: Ruft beim Absenden des BE-Formulars die 
 * Funktion al_SetEvent (siehe unten) auf.
 *
 */
array_insert($GLOBALS['TL_DCA']['tl_calendar_events']['config']['onsubmit_callback'], -1, array
    (
        array('tl_attendanceEvents', 'al_SetEvent')
    )
);


/**
 * Erweitert den Toggler der Termine um eine toggleIcon-Funktion (Aktualisiert tl_attendance)
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['toggle']['button_callback'] = array 
    (        
        'tl_attendanceEvents', 'toggleIcon'
    );

$GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['toggle']['attributes'] = array 
    (        
        'onclick="Backend.getScrollOffset();return AjaxRequest.toggleAttendance(this,%s)"'
    );

/**
 * Palettes
 *
 * Eingabemaske für die Mitglieder-Verwaltung um drei Felder erweitern, 
 * wird als vorletzte Gruppe in DCA eingefügt
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['subpalettes']['addTime'] .= ",meetingTime";


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['meetingTime'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_calendar_events']['meetingTime'],
    'default'                 => time(),
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array
    (
        'rgxp'              => 'time', 
        'doNotSaveEmpty'    => true, 
        'tl_class'          => 'clr'
    ),
    'sql'                     => "int(10) unsigned NULL"
);

// Start- und Endzeiten eines Termin können mitkopiert werden
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['startTime']['eval']['doNotCopy'] = false;
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['endTime']['eval']['doNotCopy'] = false;

/**
 * Class tl_attendanceEvents
 *
 * Zusätzliche Methode, um beim Erstellen des Moduls, die Tabelle zu befüllen
 *
 * @copyright  Sebastian Buck 2014
 * @author     Sebastian Buck
 * @package    Attendance
 */
class tl_attendanceEvents extends tl_calendar_events 
{
    /**
     * Call the "al_SetEvent" callback
     *
     * Diese Funktion wird gerufen, wenn das BE-Formular gesendet wird, und aktualisiert tl_attendance
     */
    public function al_SetEvent($dc) 
    {
        // Veröffentlichungsstatus holen und speichern
        $eventPub = $this->Input->post('published');

        // Wenn das Event veröffentlich ist
        if ($eventPub == 1) 
        {
            // Anwesenheitstabelle aktualisieren
            UpdateAttendance::al_createAttendance("all");
        }
        // sonst (wenn das Event also nicht veröffentlicht ist)
        else 
        {
            // Event wird aus tl_attendance gelöscht
            \sb_attendanceModel::deleteFromAttendanceTable('e_id', $dc->activeRecord->id);
        }
    }
    
    /* 
     * Erweiterung der toggler-Icon Funktion
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes) 
    {        
        $this->toggleAttendance($row['published'], $row['id']);        
        return parent::toggleIcon($row, $href, $label, $title, $icon, $attributes);
    }

    private function toggleAttendance($inaktiv, $eventId) 
    {   
        if ($inaktiv != 1) 
        {
            \sb_attendanceModel::deleteFromAttendanceTable('e_id', $eventId);
        } 
        else 
        {   
            UpdateAttendance::al_createAttendance("all");                   
        }
    }    

    /**
     * Call the "al_deleteEvent" callback
     *
     * Diese Funktion wird gerufen, wenn ein Event gelöscht wird, und löscht das entsprechende Event aus tl_attendance
     */
    public function al_deleteEvent($dc) 
    {
        if ($dc instanceof \DataContainer && $dc->activeRecord) 
        {
            \sb_attendanceModel::deleteFromAttendanceTable('e_id', $dc->activeRecord->id);
        }
    }
}