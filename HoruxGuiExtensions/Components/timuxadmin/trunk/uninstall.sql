DROP TABLE IF EXISTS `hr_timux_activity_counter`;
DROP TABLE IF EXISTS `hr_timux_booking`;
DROP TABLE IF EXISTS `hr_timux_booking_bde`;
DROP TABLE IF EXISTS `hr_timux_config`;
DROP TABLE IF EXISTS `hr_timux_hourly`;
DROP TABLE IF EXISTS `hr_timux_request`;
DROP TABLE IF EXISTS `hr_timux_request_leave`;
DROP TABLE IF EXISTS `hr_timux_request_workflow`;
DROP TABLE IF EXISTS `hr_timux_timeclass`;
DROP TABLE IF EXISTS `hr_timux_timecode`;
DROP TABLE IF EXISTS `hr_timux_timeunit`;
DROP TABLE IF EXISTS `hr_timux_workflow`;
DROP TABLE IF EXISTS `hr_timux_workingtime`;

DELETE FROM `hr_user_action` WHERE `catalog`='timuxadmin';
