<style>
        .notificationContainer {
            display: flex;
            flex-direction: row;
            align-items: center;
        }
        .notificationContainer h4, p, button {
            color: black;
        }
        .notificationbell {
            position: relative;
            cursor: pointer;
            margin-right: 10px;
        }
        /*.notificationbell i {*/
        /*    color: #333;*/
        /*    font-size: 245px;*/
        /*}*/

        .notificationbell #notificationBadge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #FF6347;
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 12px;
            font-weight: bold;
        }


        /* Notification Dropdown */
        /*.notification-popup {*/
        /*    position: relative;*/
        /*    margin: 0;*/
        /*    padding: 0;*/
        /*    display: flex;*/
        /*    background: yellow;*/
        /*    right:0;*/
        /*}*/

        #notificationDropdown {
            /*position: absolute;*/
            top: calc(100% - 10px);
            left: 1000px;
            width: 400px;
            max-height: 700px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 0;
            padding: 0;

        }

        .notiPopHeader {
            padding: 5px 10px;
            background-color: #f0f0f0;
            border-bottom: 1px solid #ccc;
            margin-top: 0;
        }
        .notiPopHeader h {
            margin: 0;
        }

        #notificationList .list-group-item {
            border: none;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start
        }

        #notificationList .list-group-item:hover {
            background-color: #f5f5f5;
        }

        .notificationInfo {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .notificationInfo p {
            font-size: 11px;
        }

        .list-group-item-text:hover{
            text-decoration: underline;
        }
        .mainNotiHeader div{
            display: flex;
            justify-content: space-around;
            align-content: flex-start;

        }

        .disabled-link {
            pointer-events: none;
            opacity: 0.5;
        }

        .unread-notification {
            background-color: #ccffff;
        }
        .list-group {
            position: relative;
        }
        .globalNoti {
            position: absolute;
        }

    </style>

<script>
        $(document).ready(function() {

            var pageNumber =1;
            var displayNotificationOnId = [];
            var newNotificationIds = [];
            var loading = false;
            var loadDelay = 4000;

            var bellClickedToOpen = true;


            function getNewNotifications() {

                $.ajax({
                    type:'GET',
                    url:'/notifications?page=' + pageNumber,
                    success: function (data) {
                        populateNotifications(data.notifications);
                        data.notifications.forEach(function(dta){
                            if (!newNotificationIds.includes(dta.id)) {
                                newNotificationIds.push(dta.id)

                            }
                        })
                        console.log(data)
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching new notifications:', error);
                    }
                })
            }

            // $(document).ready(function() {
            $('#notificationDropdown').on('scroll', function() {
                var container = $(this);
                var scrollPosition = container.scrollTop();
                var containerHeight = container.innerHeight();
                var contentHeight = container[0].scrollHeight;

                var distanceBottom = contentHeight - (scrollPosition + containerHeight);

                var threshold = 50;
                // Check if the scroll position is near the bottom of the container
                if (distanceBottom <=threshold && !loading ) {

                    loading = true


                    $('#loading').show();
                    console.log("inside if statement" + loading)

                    setTimeout(function () {
                        console.log("reached bottom of the popup")
                        pageNumber +=1;
                        getNewNotifications(pageNumber); // Fetch new notifications when near the bottom
                        loading = false;
                        $('#loading').hide;

                    }, loadDelay)

                }
            });
            // });


            function fetchNotificationCount () {
                $.ajax({
                    url : '/notifications',
                    method: 'GET',
                    success: function (data) {
                        var notifications = data.notifications;
                        if(data.newNotificationsCount>0){
                            $('#notificationBadge').text(data.newNotificationsCount).show();
                            $("#readAll").removeClass("disabled-link");
                            // console.log(">0" + data.newNotificationsCount);
                        } else {
                            $('#notificationBadge').hide();
                            $("#readAll").addClass("disabled-link");
                            // console.log("else" + data.newNotificationsCount);
                        }
                        // $('#notificationBell').click(function() {
                        populateNotifications(notifications);

                    }, error: function (xhr, status, error) {
                        console.error(error);
                    }
                })
            }

            fetchNotificationCount();
            setInterval(fetchNotificationCount, 5000);


            function populateNotifications(notifications) {
                // var $notificationList = $('#notificationList');
                // $notificationList.empty(); // Clear existing notifications
                var globalNotificationDiv = $('#global-notification');
                var hasGlobalNotification = false;

                console.log(notifications);

                var globalNotifications = notifications.filter(function (notification){
                    return notification.type === 'global';
                })

                if(globalNotifications && !displayNotificationOnId.includes(globalNotifications.id)) {
                    console.log(globalNotifications)
                    // console.log(globalNotifications.expired)

                    displayNotification(globalNotifications, true)
                }

 

                for (var i=0; i<notifications.length; i++) {
                    var notification = notifications[i];
                    var buttonText = notification.read === 0 ? "Unread" : "Read";
                    var notificationClass = notification.read === 0 ? "unread-notification" : "";

                    if (!displayNotificationOnId.includes(notification.id)) {
                        displayNotification(notification, false);
                        console.log(displayNotificationOnId, notification.id);
                    }
                }

                $('#notificationBell').click(function (){
                    $('#notificationDropdown').show();

                })


            }



            function displayNotification (notification, isGlobal) {
                var $notificationList = $('#notificationList');
                var globalNotificationDiv = $('#global-notification');
                var greaterThanLargestId= false;

                var notificationRead = notification.read ===0 ? "unread-notification" : "";
                var disableClick = isGlobal ? "disable-globalNotification" : "";

                var daNotifications = `
                        <div class="list-group-item ${notificationRead} ${disableClick}"  >


                            <a  href= "${notification.url}" class="mainNotiHeader" data-notification-global="${notification.type}"   data-notification-id="${notification.id}" data-notification-read="${notification.read}" >
                                <div class = "notificationInfo">
                                    <h4  class="list-group-item-heading">${notification.tool}</h4>
                                    <p >${notification.created_at}</p>
                                </div>
                                <p class="list-group-item-text" >${notification.text}</p>
                            </a>



                        </div>

`;

                if (!displayNotificationOnId.includes(notification.id)) {

                    for (var i = 0; i<displayNotificationOnId.length; i++){

                        if (notification.id >displayNotificationOnId[i]){
                            greaterThanLargestId = true;
                            break;
                        }

                    }
                    if (greaterThanLargestId) {


                        $notificationList.prepend(daNotifications);
                        greaterThanLargestId= false;
                        displayNotificationOnId.push(notification.id);
                        console.log("line 469" + displayNotificationOnId + " added notiID" + notification.id );


                    }

                    if(isGlobal) {
                        globalNotificationDiv.empty();
                        globalNotificationDiv.html(daNotifications);
                        globalNotificationDiv.find('.disable-globalNotification').click(function (event){
                            event.preventDefault();
                        })

                    } else
                    if (greaterThanLargestId === false) {
                        $notificationList.append(daNotifications);

                        console.log("line 481 " +greaterThanLargestId  + displayNotificationOnId  + "" + notification.id)

                    }
                    displayNotificationOnId.push(notification.id);
                }


                if (isGlobal) {
                    globalNotificationDiv.find('.disable-globalNotification').click(function (event){
                        event.preventDefault();
                    })
                }


                console.log("line 490" + displayNotificationOnId );


            }

            $(document).on('mousedown', function(event) {
                var $notificationDropdown = $('#notificationDropdown');
                var $notificationBell = $('#notificationBell');


                if (
                    !$(event.target).closest('#notificationDropdown').length &&
                    !$(event.target).closest('#notificationBell').length
                ) {
                    $notificationDropdown.hide(); // Hide the dropdown
                    // var pageNumber =1;
                }
            });




            




        });





    </script>

<ul> 
<li style="margin-right: 10px" class="notificationContainer">
                        <div id="notificationBell" class="notificationbell">
                            <i class="fa fa-bell" style="color: white; font-size: 24px;"></i>
                            <span id="notificationBadge"  style="display: none;"></span>
                        </div>

                        {{--                        <div class="notification-popup">--}}
                        <div id="notificationDropdown" class="dropdown-menu">
                            <div class="notiPopHeader">
                                <h1 >Notifications</h1>
                            </div>

                            <span  tabindex="0" style="color: black; margin: 0; padding-bottom: 0 " id="readAll" class="btn btn-link"  >Read All</span>

                            <div id="notificationList" class="list-group">
                                <div id="global-notification"> </div>
                            </div>
                            <h2 id="loading">loading...</h2>


                        </div>

</div>




</li>

</ul>

 


