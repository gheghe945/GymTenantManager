<?php $title = 'Inviti Utenti - GymManager'; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2>Invita Utenti</h2>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#inviteModal">
            <i class="fas fa-user-plus"></i> Nuovo Invito
        </button>
    </div>
    <div class="card-body">
        <?php flash('invite_message'); ?>
        
        <?php if (!$smtpConfigured): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Le impostazioni SMTP non sono configurate. Gli inviti saranno creati ma non verranno inviati via email.
                <a href="<?= URLROOT ?>/settings" class="alert-link">Configura le impostazioni SMTP</a>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table sortable">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Stato</th>
                        <th>Data di Scadenza</th>
                        <th>Data di Creazione</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invites)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Nessun invito trovato</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invites as $invite): ?>
                            <tr>
                                <td><?= $invite['email'] ?></td>
                                <td>
                                    <?php if ($invite['status'] === 'pending'): ?>
                                        <span class="badge bg-warning">In attesa</span>
                                    <?php elseif ($invite['status'] === 'used'): ?>
                                        <span class="badge bg-success">Utilizzato</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Scaduto</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($invite['expires_at'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($invite['created_at'])) ?></td>
                                <td>
                                    <?php if ($invite['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-primary copy-invite-link" 
                                                data-token="<?= $invite['token'] ?>" title="Copia Link">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info resend-invite" 
                                                data-email="<?= $invite['email'] ?>" data-token="<?= $invite['token'] ?>" title="Reinvia">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal per l'invito -->
<div class="modal fade" id="inviteModal" tabindex="-1" role="dialog" aria-labelledby="inviteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/invites/send" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteModalLabel">Invita Nuovo Utente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="inviteTypeTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="emailInvite-tab" data-toggle="tab" href="#emailInvite" role="tab">
                                <i class="fas fa-envelope"></i> Invito via Email
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="manualCreate-tab" data-toggle="tab" href="#manualCreate" role="tab">
                                <i class="fas fa-user-plus"></i> Creazione Manuale
                            </a>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-3" id="inviteTypeContent">
                        <!-- Tab Invito via Email -->
                        <div class="tab-pane fade show active" id="emailInvite" role="tabpanel">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                                <small class="form-text text-muted">Verrà generato un link di invito per questo indirizzo email.</small>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input type="checkbox" name="send_email" id="send_email" class="form-check-input" checked>
                                <label for="send_email" class="form-check-label">Invia link di registrazione via email</label>
                                <small class="form-text text-muted">
                                    <?php if ($smtpConfigured): ?>
                                        L'utente riceverà un'email con un link per completare la registrazione.
                                    <?php else: ?>
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            Le impostazioni SMTP non sono configurate. L'email non potrà essere inviata.
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Tab Creazione Manuale -->
                        <div class="tab-pane fade" id="manualCreate" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                Crea un nuovo utente direttamente, senza che l'utente debba registrarsi.
                                Verrà generata una password casuale.
                            </div>
                            
                            <input type="hidden" name="manual_registration" value="1">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="manual_email">Email</label>
                                        <input type="email" name="email" id="manual_email" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role">Ruolo</label>
                                        <select name="role" id="role" class="form-control">
                                            <option value="MEMBER">Membro</option>
                                            <option value="GYM_ADMIN">Amministratore</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Nome</label>
                                        <input type="text" name="name" id="name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lastname">Cognome</label>
                                        <input type="text" name="lastname" id="lastname" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check mt-3">
                                <input type="checkbox" name="send_email" id="send_credentials_email" class="form-check-input" checked>
                                <label for="send_credentials_email" class="form-check-label">Invia credenziali via email</label>
                                <small class="form-text text-muted">
                                    <?php if ($smtpConfigured): ?>
                                        L'utente riceverà un'email con le credenziali di accesso.
                                    <?php else: ?>
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            Le impostazioni SMTP non sono configurate. L'email non potrà essere inviata.
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> <span id="submitBtnText">Invia Invito</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copia il link di invito
    document.querySelectorAll('.copy-invite-link').forEach(function(button) {
        button.addEventListener('click', function() {
            const token = this.getAttribute('data-token');
            const inviteLink = '<?= URLROOT ?>/register/' + token;
            
            // Crea un campo di testo temporaneo
            const tempInput = document.createElement('input');
            tempInput.value = inviteLink;
            document.body.appendChild(tempInput);
            
            // Seleziona e copia il testo
            tempInput.select();
            document.execCommand('copy');
            
            // Rimuovi il campo temporaneo
            document.body.removeChild(tempInput);
            
            // Notifica l'utente
            alert('Link di invito copiato negli appunti');
        });
    });
    
    // Reinvia l'invito
    document.querySelectorAll('.resend-invite').forEach(function(button) {
        button.addEventListener('click', function() {
            const email = this.getAttribute('data-email');
            if (confirm('Vuoi reinviare l\'invito a ' + email + '?')) {
                // TODO: implementare la logica per reinviare l'invito
                alert('Funzionalità non ancora implementata');
            }
        });
    });
});
</script>