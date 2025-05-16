<?php $title = 'Impostazioni SMTP Globali - GymManager'; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-envelope"></i> Impostazioni SMTP Globali</h2>
    </div>
    <div class="card-body">
        <?php flash('smtp_message'); ?>
        
        <p class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Queste impostazioni SMTP globali verranno utilizzate come fallback quando le palestre (tenant) non hanno configurato le proprie impostazioni SMTP.
        </p>
        
        <form action="<?= URLROOT ?>/global-smtp/save" method="post">
            <input type="hidden" name="id" value="<?= $settings['id'] ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="host">Host SMTP <span class="text-danger">*</span></label>
                        <input type="text" name="host" id="host" class="form-control" value="<?= $settings['host'] ?>" required>
                        <small class="form-text text-muted">Es. smtp.gmail.com, smtp.office365.com</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="port">Porta SMTP <span class="text-danger">*</span></label>
                        <input type="number" name="port" id="port" class="form-control" value="<?= $settings['port'] ?>" required>
                        <small class="form-text text-muted">Porte comuni: 25, 465 (SSL), 587 (TLS)</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="encryption">Crittografia</label>
                        <select name="encryption" id="encryption" class="form-control">
                            <option value="tls" <?= $settings['encryption'] === 'tls' ? 'selected' : '' ?>>TLS</option>
                            <option value="ssl" <?= $settings['encryption'] === 'ssl' ? 'selected' : '' ?>>SSL</option>
                            <option value="" <?= $settings['encryption'] === '' ? 'selected' : '' ?>>Nessuna</option>
                        </select>
                        <small class="form-text text-muted">Il tipo di crittografia da utilizzare</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="username">Username/Email <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control" value="<?= $settings['username'] ?>" required>
                        <small class="form-text text-muted">Solitamente l'indirizzo email completo</small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="password">Password <?= empty($settings['password']) ? '<span class="text-danger">*</span>' : '' ?></label>
                        <input type="password" name="password" id="password" class="form-control" <?= empty($settings['password']) ? 'required' : '' ?>>
                        <small class="form-text text-muted">
                            <?php if (!empty($settings['password'])): ?>
                                Lascia vuoto per mantenere la password attuale
                            <?php else: ?>
                                Password per l'account SMTP
                            <?php endif; ?>
                        </small>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="active" id="active" <?= $settings['active'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active">Attivo</label>
                        <small class="form-text text-muted d-block">
                            Abilita questo server SMTP come fallback quando i tenant non hanno la propria configurazione
                        </small>
                    </div>
                </div>
            </div>
            
            <hr>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="sender_name">Nome Mittente <span class="text-danger">*</span></label>
                        <input type="text" name="sender_name" id="sender_name" class="form-control" value="<?= $settings['sender_name'] ?>" required>
                        <small class="form-text text-muted">Il nome che apparirà come mittente delle email</small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="sender_email">Email Mittente <span class="text-danger">*</span></label>
                        <input type="email" name="sender_email" id="sender_email" class="form-control" value="<?= $settings['sender_email'] ?>" required>
                        <small class="form-text text-muted">L'indirizzo email che apparirà come mittente</small>
                    </div>
                </div>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salva Impostazioni
                </button>
                
                <button type="button" class="btn btn-info ms-2" id="test-connection">
                    <i class="fas fa-vial"></i> Testa Connessione
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Test della connessione
    document.getElementById('test-connection').addEventListener('click', function() {
        // Ottieni i dati dal form
        const host = document.getElementById('host').value;
        const port = document.getElementById('port').value;
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        const encryption = document.getElementById('encryption').value;
        
        // Validazione
        if (!host || !username) {
            alert('I campi Host e Username sono obbligatori per testare la connessione.');
            return;
        }
        
        // Crea un form nascosto
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '<?= URLROOT ?>/global-smtp/test';
        form.style.display = 'none';
        
        // Aggiungi i campi
        const fields = {
            'host': host,
            'port': port,
            'username': username,
            'password': password,
            'encryption': encryption
        };
        
        for (const key in fields) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];
            form.appendChild(input);
        }
        
        // Aggiungi il form al body e invia
        document.body.appendChild(form);
        form.submit();
    });
});
</script>