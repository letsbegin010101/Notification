Route::group(['middleware' => 'samlauth'], function() {
Route::get('/notifications', 'UserNotificationController@show');
Route::post('/notificationsRead/{id}', 'UserNotificationController@markSingleAsRead');
Route::post('/notificationsReadAll', 'UserNotificationController@markAllAsRead');

}