<?php
/**
 * Calendar Controller
 * Gestisce le operazioni relative al calendario dei corsi
 */
class CalendarController extends BaseController {
    /**
     * Modelli utilizzati
     */
    private $courseModel;
    private $attendanceModel;
    
    /**
     * Configurazione middleware
     */
    protected $middleware = [
        'AuthMiddleware' => ['*'],
        'RoleMiddleware' => ['*'],
        'TenantMiddleware' => ['*']
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->courseModel = new Course();
        $this->attendanceModel = new Attendance();
    }
    
    /**
     * Mostra il calendario corsi
     *
     * @return void
     */
    public function index() {
        // Solo gli admin possono accedere al calendario completo
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Ottieni tutti i corsi per questo tenant
        $courses = $this->courseModel->getCoursesByTenantId($tenantId);
        
        // Ottieni il mese/anno corrente o quelli specificati via GET
        $month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
        $year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
        
        // Assicurati che mese e anno siano validi
        if ($month < 1 || $month > 12) $month = date('m');
        if ($year < 2000 || $year > 2100) $year = date('Y');
        
        // Ottieni il primo e l'ultimo giorno del mese
        $firstDay = new DateTime("$year-$month-01");
        $lastDay = new DateTime($firstDay->format('Y-m-t'));
        
        // Formatta le date per la query
        $startDate = $firstDay->format('Y-m-d');
        $endDate = $lastDay->format('Y-m-d');
        
        // Ottieni presenze/lezioni per questo intervallo di date
        $attendance = $this->attendanceModel->getAttendanceByDateRange($tenantId, $startDate, $endDate);
        
        // Prepara i dati per il calendario
        $calendarData = $this->prepareCalendarData($courses, $attendance, $month, $year);
        
        $data = [
            'title' => __('Course Calendar'),
            'courses' => $courses,
            'calendarData' => $calendarData,
            'currentMonth' => $month,
            'currentYear' => $year,
            'prevMonth' => $month == 1 ? 12 : $month - 1,
            'prevYear' => $month == 1 ? $year - 1 : $year,
            'nextMonth' => $month == 12 ? 1 : $month + 1,
            'nextYear' => $month == 12 ? $year + 1 : $year
        ];
        
        $this->render('courses/calendar', $data);
    }
    
    /**
     * Prepara i dati per il calendario
     *
     * @param array $courses Corsi
     * @param array $attendance Presenze
     * @param int $month Mese corrente
     * @param int $year Anno corrente
     * @return array
     */
    private function prepareCalendarData($courses, $attendance, $month, $year) {
        // Crea un array con tutti i giorni del mese
        $calendar = [];
        
        // Primo giorno del mese
        $firstDay = new DateTime("$year-$month-01");
        
        // Ultimo giorno del mese
        $lastDay = new DateTime($firstDay->format('Y-m-t'));
        
        // Numero di giorni nel mese
        $daysInMonth = intval($lastDay->format('d'));
        
        // Giorno della settimana per il primo giorno (0 = domenica, 6 = sabato)
        $firstDayOfWeek = intval($firstDay->format('w'));
        
        // Mappa delle presenze per data e corso
        $attendanceMap = [];
        foreach ($attendance as $record) {
            $date = $record['date'];
            $courseId = $record['course_id'];
            
            if (!isset($attendanceMap[$date])) {
                $attendanceMap[$date] = [];
            }
            
            if (!isset($attendanceMap[$date][$courseId])) {
                $attendanceMap[$date][$courseId] = 0;
            }
            
            $attendanceMap[$date][$courseId]++;
        }
        
        // Riempimento del calendario
        $day = 1;
        $calendar = [];
        
        // Costruisci 6 settimane (max necessarie per mostrare un mese completo)
        for ($week = 0; $week < 6; $week++) {
            $calendar[$week] = [];
            
            // 7 giorni per settimana
            for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++) {
                // Giorni vuoti prima dell'inizio del mese
                if ($week == 0 && $dayOfWeek < $firstDayOfWeek) {
                    $calendar[$week][$dayOfWeek] = [
                        'day' => null,
                        'courses' => []
                    ];
                } 
                // Giorni dopo la fine del mese
                else if ($day > $daysInMonth) {
                    $calendar[$week][$dayOfWeek] = [
                        'day' => null,
                        'courses' => []
                    ];
                } 
                // Giorni validi del mese
                else {
                    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $calendar[$week][$dayOfWeek] = [
                        'day' => $day,
                        'date' => $date,
                        'courses' => [],
                        'isToday' => $date == date('Y-m-d')
                    ];
                    
                    // Aggiungi i corsi per questo giorno
                    foreach ($courses as $course) {
                        // Controlla se il corso si tiene in questo giorno della settimana
                        // Formato schedule: "Mon,Wed,Fri" o "Monday,Wednesday,Friday"
                        $daysOfWeek = explode(',', $course['schedule']);
                        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        $dayAbbr = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        
                        // Controlla se il corso è pianificato per questo giorno
                        $isScheduled = false;
                        foreach ($daysOfWeek as $scheduleDay) {
                            $scheduleDay = trim($scheduleDay);
                            if (
                                $dayOfWeek === array_search($scheduleDay, $dayNames) || 
                                $dayOfWeek === array_search($scheduleDay, $dayAbbr)
                            ) {
                                $isScheduled = true;
                                break;
                            }
                        }
                        
                        if ($isScheduled) {
                            // Numero di partecipanti per questo corso in questo giorno
                            $attendanceCount = isset($attendanceMap[$date][$course['id']]) 
                                ? $attendanceMap[$date][$course['id']] 
                                : 0;
                            
                            $calendar[$week][$dayOfWeek]['courses'][] = [
                                'id' => $course['id'],
                                'name' => $course['name'],
                                'instructor' => $course['instructor'],
                                'attendance_count' => $attendanceCount,
                                'max_capacity' => $course['max_capacity']
                            ];
                        }
                    }
                    
                    $day++;
                }
            }
        }
        
        return $calendar;
    }
    
    /**
     * Aggiungi un nuovo corso tramite chiamata AJAX
     * 
     * @return void
     */
    public function addCourseAjax() {
        // Solo admin possono aggiungere corsi
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        // Verifica che sia una richiesta POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        
        // Ottieni dati dalla richiesta
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validazione
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Il nome del corso è obbligatorio';
        }
        
        if (empty($data['instructor'])) {
            $errors['instructor'] = 'Il nome dell\'istruttore è obbligatorio';
        }
        
        if (empty($data['schedule'])) {
            $errors['schedule'] = 'La programmazione è obbligatoria';
        }
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            return;
        }
        
        // Prepara i dati
        $courseData = [
            'tenant_id' => getCurrentTenantId(),
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'instructor' => $data['instructor'],
            'schedule' => $data['schedule'],
            'max_capacity' => $data['max_capacity'] ?? 20
        ];
        
        // Aggiungi il corso
        $courseId = $this->courseModel->create($courseData);
        
        if ($courseId) {
            // Successo
            $course = $this->courseModel->getCourseById($courseId);
            echo json_encode([
                'success' => true,
                'message' => 'Corso aggiunto con successo',
                'course' => $course
            ]);
        } else {
            // Errore
            http_response_code(500);
            echo json_encode([
                'error' => 'Errore durante l\'aggiunta del corso'
            ]);
        }
    }
}