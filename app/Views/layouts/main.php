<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? __('GymManager - Multi-tenant Gym Management System') ?></title>
    
    <!-- CSS Styles -->
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/css/style.css">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Include Chart.js for reports if needed -->
    <?php if (isset($includeCharts) && $includeCharts): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <?php endif; ?>
</head>
<body class="<?= isset($bodyClass) ? $bodyClass : '' ?>">
    <?php if (!isset($hideHeader) || !$hideHeader): ?>
    <header>
        <div class="container">
            <div class="navbar">
                <?php 
                // Carica le impostazioni della palestra se l'utente è loggato
                $gymSettings = null;
                if (isLoggedIn() && isset($_SESSION['tenant_id'])) {
                    $gymSettingModel = new GymSetting();
                    $gymSettings = GymSetting::getByTenantId($_SESSION['tenant_id']);
                }
                ?>
                
                <div class="logo">
                    <?php if ($gymSettings && !empty($gymSettings['logo_path'])): ?>
                        <img src="<?= $gymSettings['logo_path'] ?>" alt="Logo" class="gym-logo">
                    <?php else: ?>
                        <i class="fas fa-dumbbell"></i>
                    <?php endif; ?>
                    
                    <?php if ($gymSettings && !empty($gymSettings['gym_name'])): ?>
                        <?= $gymSettings['gym_name'] ?>
                    <?php elseif (isset($_SESSION['tenant_id']) && isset($tenantName)): ?>
                        <?= $tenantName ?>
                    <?php else: ?>
                        <?= __('GymManager') ?>
                    <?php endif; ?>
                </div>
                
                <?php if (isLoggedIn()): ?>
                <div class="mobile-menu" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </div>
                
                <ul class="nav-links">
                    <li class="dropdown">
                        <a href="#" class="user-profile-link">
                            <?php 
                            // Recupera la foto profilo dell'utente
                            $userProfilePhoto = '/assets/images/default-profile.png'; // Immagine predefinita
                            if (isset($_SESSION['user_id'])) {
                                $userProfileModel = new UserProfile();
                                $userProfile = $userProfileModel->getByUserId($_SESSION['user_id']);
                                if ($userProfile && !empty($userProfile['profile_photo'])) {
                                    $userProfilePhoto = $userProfile['profile_photo'];
                                }
                            }
                            ?>
                            <span class="profile-photo">
                                <img src="<?= $userProfilePhoto ?>" alt="Profile" class="profile-image">
                            </span>
                            <span class="profile-name"><?= $_SESSION['user_name'] ?? __('User') ?></span>
                        </a>
                        <div class="dropdown-content">
                            <a href="<?= URLROOT ?>/profile"><i class="fas fa-user-cog"></i> <?= __('Profilo') ?></a>
                            <a href="<?= URLROOT ?>/logout"><i class="fas fa-sign-out-alt"></i> <?= __('Esci') ?></a>
                        </div>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php endif; ?>
    
    <?php if (isLoggedIn() && (!isset($hideSidebar) || !$hideSidebar)): ?>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="sidebar-toggle" id="toggle-sidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-dumbbell"></i>
                <span><?= __('GymManager') ?></span>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <?php if (hasRole('SUPER_ADMIN')): ?>
            <!-- Menu per SUPER_ADMIN -->
            <li>
                <a href="<?= URLROOT ?>/">
                    <i class="fas fa-tachometer-alt"></i>
                    <span><?= __('Dashboard') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/tenants">
                    <i class="fas fa-building"></i>
                    <span><?= __('Gyms') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/users">
                    <i class="fas fa-users"></i>
                    <span><?= __('Users') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/reports">
                    <i class="fas fa-chart-bar"></i>
                    <span><?= __('Reports') ?></span>
                </a>
            </li>
            
            <?php elseif (hasRole('GYM_ADMIN')): ?>
            <!-- Menu per GYM_ADMIN riorganizzato secondo le specifiche -->
            
            <!-- Sezione 1: Club -->
            <li>
                <a href="#" class="has-submenu">
                    <i class="fas fa-building"></i>
                    <span><?= __('Club') ?></span>
                    <i class="fas fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="<?= URLROOT ?>/">
                            <i class="fas fa-th-large"></i>
                            <span><?= __('Pannello di Controllo') ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= URLROOT ?>/payments">
                            <i class="far fa-credit-card"></i>
                            <span><?= __('Crediti') ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= URLROOT ?>/memberships">
                            <i class="far fa-clipboard"></i>
                            <span><?= __('Abbonamenti') ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= URLROOT ?>/payments/history">
                            <i class="fas fa-chart-line"></i>
                            <span><?= __('Movimenti') ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= URLROOT ?>/settings">
                            <i class="fas fa-cog"></i>
                            <span><?= __('Impostazioni') ?></span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Sezione 2: Attività -->
            <li>
                <a href="<?= URLROOT ?>/courses/calendar">
                    <i class="far fa-calendar"></i>
                    <span><?= __('Calendario') ?></span>
                </a>
            </li>
            
            <!-- Sezione 3: Utenti -->
            <li>
                <a href="#" class="has-submenu">
                    <i class="fas fa-users"></i>
                    <span><?= __('Utenti') ?></span>
                    <i class="fas fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li>
                        <a href="<?= URLROOT ?>/users">
                            <i class="fas fa-list"></i>
                            <span><?= __('Lista Utenti') ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= URLROOT ?>/users/invite">
                            <i class="fas fa-user-plus"></i>
                            <span><?= __('Invita Utente') ?></span>
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Sezione 4: API -->
            <li>
                <a href="<?= URLROOT ?>/api">
                    <i class="fas fa-code"></i>
                    <span><?= __('API') ?></span>
                </a>
            </li>

            <?php else: ?>
            <!-- Menu per MEMBER -->
            <li>
                <a href="<?= URLROOT ?>/">
                    <i class="fas fa-tachometer-alt"></i>
                    <span><?= __('Dashboard') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/courses">
                    <i class="fas fa-running"></i>
                    <span><?= __('Courses') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/memberships/my">
                    <i class="fas fa-id-card"></i>
                    <span><?= __('My Membership') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/attendance/my">
                    <i class="fas fa-clipboard-check"></i>
                    <span><?= __('My Attendance') ?></span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/payments/my">
                    <i class="fas fa-credit-card"></i>
                    <span><?= __('My Payments') ?></span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="content">
            <div class="container">
                <?php flash('user_message'); ?>
                <?php flash('course_message'); ?>
                <?php flash('membership_message'); ?>
                <?php flash('attendance_message'); ?>
                <?php flash('payment_message'); ?>
                <?php flash('tenant_message'); ?>
                
                <?= $content ?? '' ?>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <?php 
            // Carica le impostazioni della palestra se l'utente è loggato
            $gymSettings = null;
            if (isLoggedIn() && isset($_SESSION['tenant_id'])) {
                $gymSettingModel = new GymSetting();
                $gymSettings = GymSetting::getByTenantId($_SESSION['tenant_id']);
            }
            ?>
            
            <div class="footer-content">
                <?php if ($gymSettings): ?>
                <div class="footer-info">
                    <h3><?= $gymSettings['gym_name'] ?? __('GymManager') ?></h3>
                    <ul>
                        <?php if (!empty($gymSettings['address']) || !empty($gymSettings['city'])): ?>
                        <li><i class="fas fa-map-marker-alt"></i> 
                            <?= $gymSettings['address'] ?? '' ?><?= !empty($gymSettings['address']) && !empty($gymSettings['city']) ? ', ' : '' ?><?= $gymSettings['city'] ?? '' ?>
                        </li>
                        <?php endif; ?>
                        
                        <?php if (!empty($gymSettings['phone'])): ?>
                        <li><i class="fas fa-phone"></i> <?= $gymSettings['phone'] ?></li>
                        <?php endif; ?>
                        
                        <?php if (!empty($gymSettings['email'])): ?>
                        <li><i class="fas fa-envelope"></i> <?= $gymSettings['email'] ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php else: ?>
                <div class="footer-info">
                    <h3><?= __('GymManager') ?></h3>
                    <p><?= __('Sistema di gestione palestre multi-tenant') ?></p>
                </div>
                <?php endif; ?>
                
                <div class="footer-copyright">
                    <p>&copy; <?= date('Y') ?> <?= __('GymManager. Tutti i diritti riservati.') ?></p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?= URLROOT ?>/assets/js/main.js"></script>
</body>
</html>
