-- Rename old keys to new ones if they exist
UPDATE `settings` SET `setting_key` = 'app_name', `setting_label` = 'App Name' WHERE `setting_key` = 'site_name';
UPDATE `settings` SET `setting_key` = 'app_url', `setting_label` = 'App URL' WHERE `setting_key` = 'site_url';
UPDATE `settings` SET `setting_key` = 'mail_host', `setting_label` = 'SMTP Host' WHERE `setting_key` = 'smtp_host';
UPDATE `settings` SET `setting_key` = 'mail_port', `setting_label` = 'SMTP Port' WHERE `setting_key` = 'smtp_port';
UPDATE `settings` SET `setting_key` = 'mail_username', `setting_label` = 'SMTP Username' WHERE `setting_key` = 'smtp_user';
UPDATE `settings` SET `setting_key` = 'mail_password', `setting_label` = 'SMTP Password' WHERE `setting_key` = 'smtp_pass';
UPDATE `settings` SET `setting_key` = 'mail_encryption', `setting_label` = 'Encryption' WHERE `setting_key` = 'smtp_encryption';

-- Insert new keys that were missing
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `setting_type`, `setting_label`, `setting_options`) VALUES 
('panel_env', 'development', 'General', 'select', 'Environment', 'development,production'),
('slow_request_time', '1.0', 'Performance', 'text', 'Slow Request Threshold (s)', null),
('mail_driver', 'smtp', 'Email', 'text', 'Mail Driver', null),
('google_client_id', '', 'Integrations', 'text', 'Google Client ID', null),
('google_client_secret', '', 'Integrations', 'password', 'Google Client Secret', null),
('admin_ip_whitelist', '', 'Security', 'textarea', 'Admin IP Whitelist (comma separated)', null),
('social_facebook', '', 'Social', 'text', 'Facebook URL', null),
('social_github', '', 'Social', 'text', 'GitHub URL', null),
('social_instagram', '', 'Social', 'text', 'Instagram URL', null),
('social_twitter', '', 'Social', 'text', 'Twitter URL', null),
('social_vk', '', 'Social', 'text', 'VK URL', null),
('social_youtube', '', 'Social', 'text', 'YouTube URL', null);
