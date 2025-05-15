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
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= URLROOT ?>/invites/send" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="inviteModalLabel">Invita Nuovo Utente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                        <small class="form-text text-muted">Verrà inviato un invito a questo indirizzo email.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary">Invia Invito</button>
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