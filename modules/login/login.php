<?php
$AUIEO_INDEX_NAME = CATSUtility::getIndexName();
ob_start();
?>

            <div id="login">
                <?php if (!empty($this->message)): ?>
                    <div>
                        <?php if ($this->messageSuccess): ?>
                            <p class="success"><?php $this->_($this->message); ?><br /></p>
                        <?php else: ?>
                            <p class="failure"><?php $this->_($this->message); ?><br /></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div id="loginText">
                    <div class="ctr">
                        <img src="images/folder1_locked.jpg" width="64" height="64" alt="security" />
                    </div>
                    <br />
                    <span>Welcome to CandidATS!</span><br />
                    <span style="font-size: 10px;">Version <?php echo(CATSUtility::getVersion()); ?></span>

                    <?php if (ENABLE_DEMO_MODE && !($this->siteName != '' && $this->siteName != 'choose') || ($this->siteName == 'demo')): ?>
                        <br /><br />
                        <a href="javascript:void(0);" onclick="demoLogin(); return false;">Login to Demo Account</a><br />
                    <?php endif; ?>
                </div>

                <div id="formBlock">
                    <form name="loginForm" id="loginForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=login&amp;a=attemptLogin<?php if ($this->reloginVars != ''): ?>&amp;reloginVars=<?php echo($this->reloginVars); ?><?php endif; ?>" method="post" onsubmit="return checkLoginForm(document.loginForm);" autocomplete="off">
                        <div id="subFormBlock">
                            <?php if ($this->siteName != '' && $this->siteName != 'choose'): ?>
                                <?php if ($this->siteNameFull == 'error'): ?>
                                    <label>This site does not exist. Please check the URL and try again.</label>
                                    <br />
                                    <br />
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($this->siteNameFull != 'error'): ?>
                                <label id="usernameLabel" for="username">Username</label><br />
                                <input name="username" id="username" class="login-input-box" value="<?php if (isset($this->username)) $this->_($this->username); ?>" />
                                <br />

                                <label id="passwordLabel" for="password">Password</label><br />
                                <input type="password" name="password" id="password" class="login-input-box" />
                                <br />

                                <input type="submit" class="button" value="Login" />
                                <input type="reset"  id="reset" name="reset"  class="button" value="Reset" />
                                
                            <?php else: ?>
                            <br /><br />
                            <b>User</b>: <i><?php echo(DEMO_LOGIN); ?></i>, <b>Pass</b>: <i><?php echo(DEMO_PASSWORD); ?></i>
                                <br />
                                <?php if ($this->aspMode): ?>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=asp&amp;a=createsite&amp;p=0">Create Free Trial Site</a><br />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=asp&amp;a=forgotLogin&amp;p=0">Forgot Login Information</a>
                                <?php else: ?>
                                    <a href="javascript:void(0);" onclick="demoLogin(); return false;">Login to Demo Account</a><br />
                                <?php endif; ?>
                            <?php endif; ?>
                            <br /><br />
                        </div>
                    </form>
                </div>
                <div style="clear: both;"></div>
            </div>
            <br />

            <script type="text/javascript">
                <?php if ($this->siteNameFull != 'error'): ?>
                    document.loginForm.username.focus();

                    function demoLogin()
                    {
                        document.getElementById('username').value = '<?php echo(DEMO_LOGIN); ?>';
                        document.getElementById('password').value = '<?php echo(DEMO_PASSWORD); ?>';
                        document.getElementById('loginForm').submit();
                    }
                    function defaultLogin()
                    {
                        document.getElementById('username').value = 'admin';
                        document.getElementById('password').value = 'cats';
                        document.getElementById('loginForm').submit();
                    }
                <?php endif; ?>
                <?php if (isset($_GET['defaultlogin'])): ?>
                    defaultLogin();
                <?php endif; ?>
            </script>

            
<?php
$AUIEO_CONTENT=  ob_get_clean();
?>