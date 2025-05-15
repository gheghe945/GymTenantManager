<?php $title = 'Impostazioni SMTP - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Impostazioni SMTP</h2>
        <p>Configura le impostazioni del server SMTP per l'invio di email ai tuoi utenti.</p>
    </div>
    <div class="card-body">
        <?php flash('settings_message'); ?>
        
        <form id="smtp-form" action="<?= URLROOT ?>/settings/saveSmtp" method="post" class="needs-validation" novalidate>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="host">Host SMTP</label>
                        <input type="text" name="host" id="host" class="form-control <?= isset($errors['host']) ? 'is-invalid' : '' ?>" 
                               value="<?= $smtp_settings['host'] ?>" required>
                        <?php if (isset($errors['host'])): ?>
                            <div class="invalid-feedback"><?= $errors['host'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="port">Porta</label>
                        <input type="number" name="port" id="port" class="form-control <?= isset($errors['port']) ? 'is-invalid' : '' ?>" 
                               value="<?= $smtp_settings['port'] ?>" required>
                        <?php if (isset($errors['port'])): ?>
                            <div class="invalid-feedback"><?= $errors['port'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               value="<?= $smtp_settings['username'] ?>" required>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?= $errors['username'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               value="<?= $smtp_settings['password'] ?>" required>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?= $errors['password'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="sender_name">Nome Mittente</label>
                        <input type="text" name="sender_name" id="sender_name" class="form-control <?= isset($errors['sender_name']) ? 'is-invalid' : '' ?>" 
                               value="<?= $smtp_settings['sender_name'] ?>" required>
                        <?php if (isset($errors['sender_name'])): ?>
                            <div class="invalid-feedback"><?= $errors['sender_name'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="sender_email">Email Mittente</label>
                        <input type="email" name="sender_email" id="sender_email" class="form-control <?= isset($errors['sender_email']) ? 'is-invalid' : '' ?>" 
                               value="<?= $smtp_settings['sender_email'] ?>" required>
                        <?php if (isset($errors['sender_email'])): ?>
                            <div class="invalid-feedback"><?= $errors['sender_email'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="encryption">Crittografia</label>
                        <select name="encryption" id="encryption" class="form-control">
                            <option value="tls" <?= $smtp_settings['encryption'] === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= $smtp_settings['encryption'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="none" <?= $smtp_settings['encryption'] === 'none' ? 'selected' : '' ?>>Nessuna</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <div class="form-check mt-4">
                            <input type="checkbox" name="active" id="active" class="form-check-input" 
                                   <?= isset($smtp_settings['active']) && $smtp_settings['active'] ? 'checked' : '' ?>>
                            <label for="active" class="form-check-label">Attiva l'invio di email</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary">Salva Impostazioni</button>
                    <button type="button" id="test-smtp" class="btn btn-secondary">Testa Connessione</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Testa la connessione SMTP
    document.getElementById('test-smtp').addEventListener('click', function() {
        const form = document.getElementById('smtp-form');
        const formData = new FormData(form);
        const btn = this;
        
        // Cambia il testo del pulsante
        btn.textContent = 'Testando...';
        btn.disabled = true;
        
        // Fetch per testare la connessione
        fetch('<?= URLROOT ?>/settings/testSmtp', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Connessione SMTP riuscita: ' + data.message);
            } else {
                alert('Errore nella connessione SMTP: ' + data.message);
            }
            
            // Ripristina il testo del pulsante
            btn.textContent = 'Testa Connessione';
            btn.disabled = false;
        })
        .catch(error => {
            alert('Si è verificato un errore durante il test: ' + error);
            btn.textContent = 'Testa Connessione';
            btn.disabled = false;
        });
    });
});
</script>