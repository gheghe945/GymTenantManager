<?php $title = 'Impostazioni Palestra - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-building mr-2"></i><?= __('Impostazioni Palestra') ?></h2>
        <p><?= __('Personalizza le informazioni e l\'aspetto della tua palestra') ?></p>
    </div>
    <div class="card-body">
        <?php flash(); ?>
        
        <form action="<?= URLROOT ?>/gym-settings/save" method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="gym_name"><?= __('Nome Palestra') ?> <sup>*</sup></label>
                        <input type="text" name="gym_name" id="gym_name" class="form-control <?= isset($data['errors']['gym_name']) ? 'is-invalid' : '' ?>" 
                               value="<?= isset($data['gymSettings']['gym_name']) ? $data['gymSettings']['gym_name'] : '' ?>" required>
                        <?php if (isset($data['errors']['gym_name'])): ?>
                            <div class="invalid-feedback"><?= $data['errors']['gym_name'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="logo"><?= __('Logo Palestra') ?></label>
                        <?php if (isset($data['gymSettings']['logo_path']) && !empty($data['gymSettings']['logo_path'])): ?>
                            <div class="mb-2">
                                <img src="<?= $data['gymSettings']['logo_path'] ?>" alt="Logo" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="logo" id="logo" class="form-control-file" accept="image/jpeg,image/png,image/gif">
                        <small class="form-text text-muted"><?= __('Formati accettati: JPG, PNG, GIF. Dimensione massima: 2MB') ?></small>
                    </div>
                </div>
            </div>
            
            <h4 class="mt-4 mb-3"><?= __('Informazioni di contatto') ?></h4>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="address"><?= __('Indirizzo') ?></label>
                        <input type="text" name="address" id="address" class="form-control" 
                               value="<?= isset($data['gymSettings']['address']) ? $data['gymSettings']['address'] : '' ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="city"><?= __('Città') ?></label>
                        <input type="text" name="city" id="city" class="form-control" 
                               value="<?= isset($data['gymSettings']['city']) ? $data['gymSettings']['city'] : '' ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="phone"><?= __('Telefono') ?></label>
                        <input type="text" name="phone" id="phone" class="form-control" 
                               value="<?= isset($data['gymSettings']['phone']) ? $data['gymSettings']['phone'] : '' ?>">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="email"><?= __('Email') ?></label>
                        <input type="email" name="email" id="email" class="form-control <?= isset($data['errors']['email']) ? 'is-invalid' : '' ?>" 
                               value="<?= isset($data['gymSettings']['email']) ? $data['gymSettings']['email'] : '' ?>">
                        <?php if (isset($data['errors']['email'])): ?>
                            <div class="invalid-feedback"><?= $data['errors']['email'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle mr-2"></i>
                <?= __('Queste informazioni saranno visibili nell\'header e nel footer del sito. Mantieni i dati aggiornati per informare correttamente i tuoi utenti.') ?>
            </div>
            
            <div class="form-group mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> <?= __('Salva Impostazioni') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Preview logo when selected
    const logoInput = document.getElementById('logo');
    
    if (logoInput) {
        logoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Check if preview exists
                    let preview = logoInput.parentElement.querySelector('img');
                    
                    if (!preview) {
                        // Create preview if it doesn't exist
                        preview = document.createElement('img');
                        preview.classList.add('img-thumbnail');
                        preview.style.maxHeight = '100px';
                        preview.alt = 'Logo Preview';
                        
                        // Create container
                        const previewContainer = document.createElement('div');
                        previewContainer.classList.add('mb-2');
                        previewContainer.appendChild(preview);
                        
                        // Insert before the file input
                        logoInput.parentElement.insertBefore(previewContainer, logoInput);
                    }
                    
                    preview.src = e.target.result;
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script>