<?php $title = 'Impostazioni - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Impostazioni</h2>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="smtp-tab" data-toggle="tab" href="#smtp" role="tab" aria-controls="smtp" aria-selected="true">
                    <i class="fas fa-envelope"></i> Impostazioni Email
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="general-tab" data-toggle="tab" href="#general" role="tab" aria-controls="general" aria-selected="false">
                    <i class="fas fa-cog"></i> Impostazioni Generali
                </a>
            </li>
        </ul>
        
        <div class="tab-content" id="settingsTabsContent">
            <!-- Tab SMTP -->
            <div class="tab-pane fade show active p-3" id="smtp" role="tabpanel" aria-labelledby="smtp-tab">
                <h3 class="mb-4">Impostazioni Server Email (SMTP)</h3>
                
                <?php flash('smtp_success'); ?>
                <?php flash('smtp_error'); ?>
                
                <form action="<?= URLROOT ?>/settings/saveSmtp" method="post">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="smtp_host">Host SMTP <sup>*</sup></label>
                                <input type="text" name="smtp_host" id="smtp_host" class="form-control <?= isset($errors['smtp_host']) ? 'is-invalid' : '' ?>" 
                                       value="<?= isset($smtp_settings['smtp_host']) ? $smtp_settings['smtp_host'] : (isset($_SESSION['form_data']['smtp_host']) ? $_SESSION['form_data']['smtp_host'] : '') ?>">
                                <?php if (isset($errors['smtp_host'])): ?>
                                    <div class="invalid-feedback"><?= $errors['smtp_host'] ?></div>
                                <?php endif; ?>
                                <small class="form-text text-muted">Es. smtp.gmail.com</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="smtp_port">Porta SMTP <sup>*</sup></label>
                                <input type="number" name="smtp_port" id="smtp_port" class="form-control <?= isset($errors['smtp_port']) ? 'is-invalid' : '' ?>" 
                                       value="<?= isset($smtp_settings['smtp_port']) ? $smtp_settings['smtp_port'] : (isset($_SESSION['form_data']['smtp_port']) ? $_SESSION['form_data']['smtp_port'] : '587') ?>">
                                <?php if (isset($errors['smtp_port'])): ?>
                                    <div class="invalid-feedback"><?= $errors['smtp_port'] ?></div>
                                <?php endif; ?>
                                <small class="form-text text-muted">Es. 465 (SSL) o 587 (TLS)</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="smtp_username">Nome utente SMTP</label>
                                <input type="text" name="smtp_username" id="smtp_username" class="form-control" 
                                       value="<?= isset($smtp_settings['smtp_username']) ? $smtp_settings['smtp_username'] : (isset($_SESSION['form_data']['smtp_username']) ? $_SESSION['form_data']['smtp_username'] : '') ?>">
                                <small class="form-text text-muted">Solitamente il tuo indirizzo email completo</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="smtp_password">Password SMTP</label>
                                <input type="password" name="smtp_password" id="smtp_password" class="form-control" 
                                       value="<?= isset($smtp_settings['smtp_password']) ? $smtp_settings['smtp_password'] : (isset($_SESSION['form_data']['smtp_password']) ? $_SESSION['form_data']['smtp_password'] : '') ?>">
                                <small class="form-text text-muted">Password dell'account email o password dell'app</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="smtp_encryption">Crittografia</label>
                                <select name="smtp_encryption" id="smtp_encryption" class="form-control">
                                    <option value="" <?= (!isset($smtp_settings['smtp_encryption']) || $smtp_settings['smtp_encryption'] === '') ? 'selected' : '' ?>>Nessuna</option>
                                    <option value="ssl" <?= (isset($smtp_settings['smtp_encryption']) && $smtp_settings['smtp_encryption'] === 'ssl') ? 'selected' : '' ?>>SSL</option>
                                    <option value="tls" <?= (isset($smtp_settings['smtp_encryption']) && $smtp_settings['smtp_encryption'] === 'tls') ? 'selected' : '' ?>>TLS</option>
                                </select>
                                <small class="form-text text-muted">Seleziona il tipo di crittografia richiesto dal tuo server SMTP</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="smtp_from_email">Email mittente <sup>*</sup></label>
                                <input type="email" name="smtp_from_email" id="smtp_from_email" class="form-control <?= isset($errors['smtp_from_email']) ? 'is-invalid' : '' ?>" 
                                       value="<?= isset($smtp_settings['smtp_from_email']) ? $smtp_settings['smtp_from_email'] : (isset($_SESSION['form_data']['smtp_from_email']) ? $_SESSION['form_data']['smtp_from_email'] : '') ?>">
                                <?php if (isset($errors['smtp_from_email'])): ?>
                                    <div class="invalid-feedback"><?= $errors['smtp_from_email'] ?></div>
                                <?php endif; ?>
                                <small class="form-text text-muted">Indirizzo email del mittente</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="smtp_from_name">Nome mittente</label>
                                <input type="text" name="smtp_from_name" id="smtp_from_name" class="form-control" 
                                       value="<?= isset($smtp_settings['smtp_from_name']) ? $smtp_settings['smtp_from_name'] : (isset($_SESSION['form_data']['smtp_from_name']) ? $_SESSION['form_data']['smtp_from_name'] : '') ?>">
                                <small class="form-text text-muted">Nome visualizzato come mittente</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col">
                            <p><sup>*</sup> Campi obbligatori</p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salva Impostazioni
                            </button>
                            <?php if (isset($smtp_settings) && $smtp_settings): ?>
                                <button type="button" id="test-smtp" class="btn btn-info ml-2">
                                    <i class="fas fa-paper-plane"></i> Invia Email di Test
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Tab Impostazioni Generali -->
            <div class="tab-pane fade p-3" id="general" role="tabpanel" aria-labelledby="general-tab">
                <h3 class="mb-4">Impostazioni Generali</h3>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Le impostazioni generali saranno disponibili in una futura versione.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test SMTP
    const testButton = document.getElementById('test-smtp');
    if (testButton) {
        testButton.addEventListener('click', function() {
            if (confirm('Vuoi inviare un\'email di test?')) {
                // Invia la richiesta di test
                fetch('<?= URLROOT ?>/settings/testSmtp', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Email di test inviata con successo!');
                    } else {
                        alert('Errore durante l\'invio della email: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Errore durante l\'invio della richiesta: ' + error);
                });
            }
        });
    }
    
    // Pulisci le variabili di sessione
    <?php unset($_SESSION['errors'], $_SESSION['form_data']); ?>
});
</script>