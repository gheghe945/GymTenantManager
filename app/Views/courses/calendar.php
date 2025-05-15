<?php 
$title = __('Course Calendar') . ' - ' . __('GymManager');
$includeCharts = true;
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2><?= __('Course Calendar') ?></h2>
        <div class="flex gap-2">
            <button class="btn btn-primary" id="add-course-btn">
                <i class="fas fa-plus"></i> <?= __('Add Course') ?>
            </button>
            <a href="<?= URLROOT ?>/courses" class="btn btn-outline-primary">
                <i class="fas fa-list"></i> <?= __('View All Courses') ?>
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Navigazione mensile -->
        <div class="calendar-nav mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <a href="<?= URLROOT ?>/courses/calendar?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-left"></i> <?= __('Previous Month') ?>
                </a>
                
                <h3><?= date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)) ?></h3>
                
                <a href="<?= URLROOT ?>/courses/calendar?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="btn btn-outline-secondary">
                    <?= __('Next Month') ?> <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        
        <!-- Calendario -->
        <div class="calendar-container">
            <table class="calendar">
                <thead>
                    <tr>
                        <th><?= __('Sunday') ?></th>
                        <th><?= __('Monday') ?></th>
                        <th><?= __('Tuesday') ?></th>
                        <th><?= __('Wednesday') ?></th>
                        <th><?= __('Thursday') ?></th>
                        <th><?= __('Friday') ?></th>
                        <th><?= __('Saturday') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calendarData as $week): ?>
                    <tr>
                        <?php foreach ($week as $day): ?>
                        <td class="calendar-day <?= isset($day['isToday']) && $day['isToday'] ? 'today' : '' ?> <?= $day['day'] === null ? 'empty' : '' ?>">
                            <?php if ($day['day'] !== null): ?>
                            <div class="day-number"><?= $day['day'] ?></div>
                            
                            <div class="day-courses">
                                <?php if (!empty($day['courses'])): ?>
                                    <?php foreach ($day['courses'] as $course): ?>
                                    <div class="calendar-event" data-course-id="<?= $course['id'] ?>">
                                        <div class="event-title"><?= $course['name'] ?></div>
                                        <div class="event-instructor"><?= $course['instructor'] ?></div>
                                        <div class="event-capacity">
                                            <span class="<?= $course['attendance_count'] >= $course['max_capacity'] ? 'text-danger' : 'text-success' ?>">
                                                <?= $course['attendance_count'] ?>/<?= $course['max_capacity'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <!-- Pulsante per aggiungere un corso in questo giorno -->
                                <button class="add-course-day-btn" data-date="<?= $day['date'] ?>">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal per aggiungere un nuovo corso -->
<div class="modal" id="add-course-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?= __('Add New Course') ?></h4>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-course-form">
                    <div class="form-group">
                        <label for="course-name"><?= __('Course Name') ?></label>
                        <input type="text" id="course-name" name="name" class="form-control" required>
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="course-description"><?= __('Description') ?></label>
                        <textarea id="course-description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="course-instructor"><?= __('Instructor') ?></label>
                        <input type="text" id="course-instructor" name="instructor" class="form-control" required>
                        <div class="invalid-feedback" id="instructor-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label><?= __('Schedule (Days of Week)') ?></label>
                        <div class="days-of-week">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="day-mon" name="days[]" value="Mon">
                                <label class="form-check-label" for="day-mon"><?= __('Mon') ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="day-tue" name="days[]" value="Tue">
                                <label class="form-check-label" for="day-tue"><?= __('Tue') ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="day-wed" name="days[]" value="Wed">
                                <label class="form-check-label" for="day-wed"><?= __('Wed') ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="day-thu" name="days[]" value="Thu">
                                <label class="form-check-label" for="day-thu"><?= __('Thu') ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="day-fri" name="days[]" value="Fri">
                                <label class="form-check-label" for="day-fri"><?= __('Fri') ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="day-sat" name="days[]" value="Sat">
                                <label class="form-check-label" for="day-sat"><?= __('Sat') ?></label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="day-sun" name="days[]" value="Sun">
                                <label class="form-check-label" for="day-sun"><?= __('Sun') ?></label>
                            </div>
                        </div>
                        <div class="invalid-feedback" id="schedule-error"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="course-capacity"><?= __('Maximum Capacity') ?></label>
                        <input type="number" id="course-capacity" name="max_capacity" class="form-control" min="1" value="20" required>
                    </div>
                    
                    <input type="hidden" id="selected-date" name="selected_date" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal"><?= __('Cancel') ?></button>
                <button type="button" class="btn btn-primary" id="save-course-btn"><?= __('Save Course') ?></button>
            </div>
        </div>
    </div>
</div>

<style>
/* Stili per il calendario */
.calendar {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.calendar th {
    padding: 10px;
    background-color: var(--gray);
    text-align: center;
    font-weight: bold;
}

.calendar-day {
    height: 150px;
    border: 1px solid var(--gray-dark);
    vertical-align: top;
    padding: 5px;
    position: relative;
    background-color: white;
}

.calendar-day.empty {
    background-color: var(--gray-light);
}

.calendar-day.today {
    background-color: rgba(52, 152, 219, 0.1);
}

.day-number {
    font-weight: bold;
    margin-bottom: 10px;
}

.day-courses {
    overflow-y: auto;
    max-height: 110px;
}

.calendar-event {
    background-color: var(--primary-color);
    color: white;
    padding: 5px;
    border-radius: 4px;
    margin-bottom: 5px;
    font-size: 0.8rem;
    cursor: pointer;
}

.event-title {
    font-weight: bold;
}

.event-instructor {
    font-size: 0.75rem;
    opacity: 0.9;
}

.event-capacity {
    text-align: right;
    font-size: 0.75rem;
}

.add-course-day-btn {
    position: absolute;
    bottom: 5px;
    right: 5px;
    border: none;
    background: transparent;
    color: var(--primary-color);
    cursor: pointer;
    font-size: 0.9rem;
    opacity: 0.5;
    transition: opacity 0.3s;
}

.add-course-day-btn:hover {
    opacity: 1;
}

/* Stili per il modale */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    overflow: auto;
}

.modal.show {
    display: block;
}

.modal-dialog {
    max-width: 600px;
    margin: 50px auto;
}

.modal-content {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 15px;
    border-bottom: 1px solid var(--gray);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
}

.close-modal {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-dark);
}

.modal-body {
    padding: 15px;
}

.modal-footer {
    padding: 15px;
    border-top: 1px solid var(--gray);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.days-of-week {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elementi del DOM
    const addCourseBtn = document.getElementById('add-course-btn');
    const addCourseModal = document.getElementById('add-course-modal');
    const closeModalBtns = document.querySelectorAll('.close-modal');
    const saveCourseBtn = document.getElementById('save-course-btn');
    const addCourseDayBtns = document.querySelectorAll('.add-course-day-btn');
    const selectedDateInput = document.getElementById('selected-date');
    
    // Apri il modale dal pulsante principale
    if (addCourseBtn) {
        addCourseBtn.addEventListener('click', function() {
            // Reset form
            document.getElementById('add-course-form').reset();
            
            // Mostra il modale
            addCourseModal.classList.add('show');
        });
    }
    
    // Apri il modale dai pulsanti giornalieri
    addCourseDayBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Reset form
            document.getElementById('add-course-form').reset();
            
            // Ottieni la data selezionata dal pulsante
            const selectedDate = this.getAttribute('data-date');
            selectedDateInput.value = selectedDate;
            
            // Se la data è di un lunedì, seleziona automaticamente il checkbox del lunedì
            const dateObj = new Date(selectedDate);
            const dayOfWeek = dateObj.getDay(); // 0 = domenica, 1 = lunedì, ...
            
            // Seleziona il checkbox corrispondente al giorno della settimana
            const dayMap = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            const checkbox = document.getElementById(`day-${dayMap[dayOfWeek]}`);
            if (checkbox) {
                checkbox.checked = true;
            }
            
            // Mostra il modale
            addCourseModal.classList.add('show');
        });
    });
    
    // Chiudi il modale
    closeModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            addCourseModal.classList.remove('show');
        });
    });
    
    // Chiudi il modale se si fa clic all'esterno
    window.addEventListener('click', function(event) {
        if (event.target === addCourseModal) {
            addCourseModal.classList.remove('show');
        }
    });
    
    // Salva il corso
    if (saveCourseBtn) {
        saveCourseBtn.addEventListener('click', function() {
            // Raccogli i dati dal form
            const nameInput = document.getElementById('course-name');
            const descriptionInput = document.getElementById('course-description');
            const instructorInput = document.getElementById('course-instructor');
            const capacityInput = document.getElementById('course-capacity');
            
            // Raccogli i giorni selezionati
            const daysChecked = document.querySelectorAll('input[name="days[]"]:checked');
            const days = Array.from(daysChecked).map(cb => cb.value);
            
            // Validazione base
            let isValid = true;
            
            if (!nameInput.value.trim()) {
                document.getElementById('name-error').textContent = 'Il nome del corso è obbligatorio';
                nameInput.classList.add('is-invalid');
                isValid = false;
            } else {
                nameInput.classList.remove('is-invalid');
            }
            
            if (!instructorInput.value.trim()) {
                document.getElementById('instructor-error').textContent = 'Il nome dell\'istruttore è obbligatorio';
                instructorInput.classList.add('is-invalid');
                isValid = false;
            } else {
                instructorInput.classList.remove('is-invalid');
            }
            
            if (days.length === 0) {
                document.getElementById('schedule-error').textContent = 'Seleziona almeno un giorno';
                document.querySelector('.days-of-week').classList.add('is-invalid');
                isValid = false;
            } else {
                document.querySelector('.days-of-week').classList.remove('is-invalid');
            }
            
            if (!isValid) return;
            
            // Prepara i dati da inviare
            const courseData = {
                name: nameInput.value.trim(),
                description: descriptionInput.value.trim(),
                instructor: instructorInput.value.trim(),
                schedule: days.join(','),
                max_capacity: capacityInput.value
            };
            
            // Invia i dati tramite AJAX
            fetch('<?= URLROOT ?>/calendar/addCourseAjax', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(courseData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostra il messaggio di successo
                    alert(data.message);
                    
                    // Chiudi il modale
                    addCourseModal.classList.remove('show');
                    
                    // Ricarica la pagina per mostrare il nuovo corso
                    window.location.reload();
                } else if (data.errors) {
                    // Mostra gli errori di validazione
                    if (data.errors.name) {
                        document.getElementById('name-error').textContent = data.errors.name;
                        nameInput.classList.add('is-invalid');
                    }
                    
                    if (data.errors.instructor) {
                        document.getElementById('instructor-error').textContent = data.errors.instructor;
                        instructorInput.classList.add('is-invalid');
                    }
                    
                    if (data.errors.schedule) {
                        document.getElementById('schedule-error').textContent = data.errors.schedule;
                        document.querySelector('.days-of-week').classList.add('is-invalid');
                    }
                } else {
                    // Errore generico
                    alert('Si è verificato un errore durante l\'aggiunta del corso');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Si è verificato un errore durante l\'aggiunta del corso');
            });
        });
    }
    
    // Clic su un evento del calendario per visualizzare i dettagli (implementazione futura)
    const calendarEvents = document.querySelectorAll('.calendar-event');
    calendarEvents.forEach(event => {
        event.addEventListener('click', function() {
            const courseId = this.getAttribute('data-course-id');
            alert('Visualizzazione dettagli del corso: ' + courseId + '\n(Funzionalità in fase di sviluppo)');
        });
    });
});
</script>