<?php $title = 'Dettaglio Invito - GymManager'; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2>Dettaglio Invito</h2>
        <a href="<?= URLROOT ?>/invites" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Torna alla lista
        </a>
    </div>
    <div class="card-body">
        <?php flash('invite_message'); ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="invite-details">
                    <h4 class="mb-3">Informazioni</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th>Email</th>
                                <td><?= $invite['email'] ?></td>
                            </tr>
                            <tr>
                                <th>Stato</th>
                                <td>
                                    <?php if ($invite['status'] === 'pending'): ?>
                                        <span class="badge bg-warning">In attesa</span>
                                    <?php elseif ($invite['status'] === 'used'): ?>
                                        <span class="badge bg-success">Utilizzato</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Scaduto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Data di Scadenza</th>
                                <td><?= date('d/m/Y H:i', strtotime($invite['expires_at'])) ?></td>
                            </tr>
                            <tr>
                                <th>Data di Creazione</th>
                                <td><?= date('d/m/Y H:i', strtotime($invite['created_at'])) ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $qrCodePath)): ?>
                        <div class="mt-4">
                            <h4 class="mb-3">Link di Registrazione</h4>
                            <div class="input-group mb-3">
                                <input type="text" id="invite-url" class="form-control" value="<?= $inviteUrl ?>" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" id="copy-url" data-tooltip="Copia">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-text text-muted mb-2">
                                Questo è il link che l'utente può utilizzare per registrarsi.
                            </div>
                            
                            <?php if ($invite['status'] === 'pending'): ?>
                                <div class="d-grid gap-2 d-md-block mt-3">
                                    <button class="btn btn-primary" id="btn-send-email">
                                        <i class="fas fa-paper-plane"></i> Invia via Email
                                    </button>
                                    <a href="<?= $inviteUrl ?>" target="_blank" class="btn btn-info">
                                        <i class="fas fa-external-link-alt"></i> Apri Link
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . $qrCodePath)): ?>
                    <div class="qr-code-container text-center">
                        <h4 class="mb-3">QR Code</h4>
                        <div class="qr-image mb-3">
                            <img src="<?= $qrCodePath ?>" alt="QR Code per la registrazione" class="img-fluid">
                        </div>
                        <div class="qr-actions">
                            <a href="<?= $qrCodePath ?>" class="btn btn-success" download="qrcode-<?= $invite['token'] ?>.png">
                                <i class="fas fa-download"></i> Scarica QR Code
                            </a>
                            <button class="btn btn-primary" id="btn-print-qr">
                                <i class="fas fa-print"></i> Stampa QR Code
                            </button>
                        </div>
                        <div class="form-text text-muted mt-2">
                            Scansiona questo QR code con uno smartphone per accedere direttamente alla pagina di registrazione.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> QR Code non disponibile.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Script per la pagina -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funzione per copiare il link
    const copyUrlBtn = document.getElementById('copy-url');
    if (copyUrlBtn) {
        copyUrlBtn.addEventListener('click', function() {
            const inviteUrlInput = document.getElementById('invite-url');
            inviteUrlInput.select();
            document.execCommand('copy');
            
            // Cambia il tooltip per dare feedback all'utente
            this.setAttribute('data-original-title', 'Copiato!');
            $(this).tooltip('show');
            
            setTimeout(() => {
                this.setAttribute('data-original-title', 'Copia');
            }, 2000);
        });
    }
    
    // Funzione per stampare il QR code
    const printQrBtn = document.getElementById('btn-print-qr');
    if (printQrBtn) {
        printQrBtn.addEventListener('click', function() {
            const qrImage = document.querySelector('.qr-image img').src;
            const inviteUrl = document.getElementById('invite-url').value;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>QR Code</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            text-align: center;
                            padding: 20px;
                        }
                        .container {
                            max-width: 500px;
                            margin: 0 auto;
                        }
                        .qr-image {
                            margin: 20px 0;
                        }
                        .qr-image img {
                            max-width: 300px;
                        }
                        .url {
                            font-size: 12px;
                            word-break: break-all;
                            margin-top: 20px;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h2>QR Code per la registrazione</h2>
                        <div class="qr-image">
                            <img src="${qrImage}" alt="QR Code">
                        </div>
                        <p>Scansiona questo QR code per accedere alla pagina di registrazione.</p>
                        <div class="url">
                            <p>URL: ${inviteUrl}</p>
                        </div>
                    </div>
                    <script>
                        window.onload = function() {
                            window.print();
                        }
                    </script>
                </body>
                </html>
            `);
            
            printWindow.document.close();
        });
    }
    
    // Funzione per inviare l'invito via email
    const sendEmailBtn = document.getElementById('btn-send-email');
    if (sendEmailBtn) {
        sendEmailBtn.addEventListener('click', function() {
            if (confirm('Vuoi inviare l\'invito via email a <?= $invite['email'] ?>?')) {
                // Effettua una richiesta AJAX per inviare l'email
                fetch('<?= URLROOT ?>/invites/sendEmail/<?= $invite['token'] ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Email inviata con successo!');
                    } else {
                        alert('Errore nell\'invio dell\'email: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Errore durante l\'invio della richiesta: ' + error);
                });
            }
        });
    }
    
    // Inizializza i tooltip
    $('[data-tooltip]').tooltip({
        title: function() {
            return $(this).attr('data-tooltip');
        }
    });
});
</script>