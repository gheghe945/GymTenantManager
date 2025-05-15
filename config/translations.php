<?php
/**
 * File di traduzione
 * Contiene le traduzioni per l'interfaccia utente
 */

// Traduzioni italiano
$translations = [
    // Comuni
    'Sign In' => 'Accedi',
    'Login' => 'Accedi',
    'Logout' => 'Esci',
    'Email' => 'Email',
    'Password' => 'Password',
    'Dashboard' => 'Pannello di Controllo',
    'Save' => 'Salva',
    'Cancel' => 'Annulla',
    'Edit' => 'Modifica',
    'Delete' => 'Elimina',
    'Actions' => 'Azioni',
    'Name' => 'Nome',
    'Description' => 'Descrizione',
    'Status' => 'Stato',
    'Date' => 'Data',
    'Submit' => 'Invia',
    'Active' => 'Attivo',
    'Inactive' => 'Inattivo',
    'Expired' => 'Scaduto',
    'Cancelled' => 'Annullato',

    // Menu
    'Gyms' => 'Palestre',
    'Users' => 'Utenti',
    'Courses' => 'Corsi',
    'Memberships' => 'Abbonamenti',
    'Attendance' => 'Presenze',
    'Payments' => 'Pagamenti',
    'Reports' => 'Report',

    // Titoli pagine
    'Create New User' => 'Crea Nuovo Utente',
    'Edit User' => 'Modifica Utente',
    'Create New Gym' => 'Crea Nuova Palestra',
    'Edit Gym' => 'Modifica Palestra',
    'Create New Course' => 'Crea Nuovo Corso',
    'Edit Course' => 'Modifica Corso',
    'Create New Membership' => 'Crea Nuovo Abbonamento',
    'Edit Membership' => 'Modifica Abbonamento',
    'Record Attendance' => 'Registra Presenza',
    'Create New Payment' => 'Crea Nuovo Pagamento',

    // Tenant (Palestra)
    'GymManager' => 'Gestione Palestre',
    'Gym Details' => 'Dettagli Palestra',
    'Gym Name' => 'Nome Palestra',
    'Subdomain' => 'Sottodominio',
    'Address' => 'Indirizzo',
    'Phone' => 'Telefono',
    'Is Active' => 'Attiva',
    'Gym Administrator' => 'Amministratore Palestra',
    'Assign Administrator' => 'Assegna Amministratore',

    // Utenti
    'User Details' => 'Dettagli Utente',
    'User Name' => 'Nome Utente',
    'Role' => 'Ruolo',
    'SUPER_ADMIN' => 'Super Amministratore',
    'GYM_ADMIN' => 'Amministratore Palestra',
    'MEMBER' => 'Membro',
    'Created' => 'Creato',
    'Last Updated' => 'Ultimo Aggiornamento',
    'Add User' => 'Aggiungi Utente',

    // Corsi
    'Course Details' => 'Dettagli Corso',
    'Course Name' => 'Nome Corso',
    'Instructor' => 'Istruttore',
    'Schedule' => 'Orario',
    'Max Capacity' => 'Capacità Massima',
    'Add Course' => 'Aggiungi Corso',

    // Abbonamenti
    'Membership Details' => 'Dettagli Abbonamento',
    'Membership Type' => 'Tipo Abbonamento',
    'Member' => 'Membro',
    'Start Date' => 'Data Inizio',
    'End Date' => 'Data Fine',
    'Price' => 'Prezzo',
    'Notes' => 'Note',
    'Add Membership' => 'Aggiungi Abbonamento',

    // Presenze
    'Attendance Records' => 'Registro Presenze',
    'Record New Attendance' => 'Registra Nuova Presenza',
    'Member' => 'Membro',
    'Course' => 'Corso',
    'Time In' => 'Orario Entrata',
    'Time Out' => 'Orario Uscita',
    
    // Pagamenti
    'Payment Details' => 'Dettagli Pagamento',
    'Amount' => 'Importo',
    'Payment Date' => 'Data Pagamento',
    'Payment Method' => 'Metodo Pagamento',
    'Add Payment' => 'Aggiungi Pagamento',
    'Cash' => 'Contanti',
    'Credit Card' => 'Carta di Credito',
    'Bank Transfer' => 'Bonifico Bancario',

    // Report
    'Attendance Report' => 'Report Presenze',
    'Membership Report' => 'Report Abbonamenti',
    'Revenue Report' => 'Report Incassi',
    'Start Date' => 'Data Inizio',
    'End Date' => 'Data Fine',
    'Generate Report' => 'Genera Report',
    
    // Messaggi
    'Invalid email or password' => 'Email o password non validi',
    'You do not have permission to access this resource' => 'Non hai il permesso di accedere a questa risorsa',
    'Record saved successfully' => 'Registrazione salvata con successo',
    'Record deleted successfully' => 'Registrazione eliminata con successo',
    'Error saving record' => 'Errore durante il salvataggio',
    'Error deleting record' => 'Errore durante l\'eliminazione'
];

/**
 * Funzione di traduzione
 * 
 * @param string $text Testo da tradurre
 * @return string Testo tradotto
 */
function __(string $text): string {
    global $translations;
    return $translations[$text] ?? $text;
}