<template>
    <div class="container">
        <project-nav>
            <span><i class="fa fa-angle-right"></i></span>
            <router-link :to="'/projects/' + $route.params.projectid + '/messages'" class="link-style t-d-none">
                {{ i18n.message_heading }}
            </router-link>
        </project-nav>
        <div class="lists border-for-nav">
            <div class="row">
                <div class="col-12">
                    <div class="text-center" v-if="loading">
                        <i class="fa fa-refresh fa-spin fa-3x fa-fw" aria-hidden="true"></i>
                        <p>{{ i18n.loading }}</p>
                    </div>

                    <div v-if="messageObject && !loading">
                        <div v-if="isShowEdit">
                            <router-link :to="'/projects/' + $route.params.projectid + '/messages/' + messageObject.ID + '/edit'" class="button button-default">
                                {{ i18n.edit }}
                            </router-link>
                            <span style="float:right" @click="deleteMessage(messageObject)">
                                <a style="color: #d54e21;cursor:pointer;">{{ i18n.delete }}</a>
                            </span>
                        </div>
                        <br>
                        <div class="message-content">
                            <div class="text-center message-by">
                                <img :src="messageObject.avatar_url" class="small-round-image" alt="">
                                <p>
                                    <i>{{ i18n.posted_by }} <strong>{{messageObject.user_name}}</strong>
                                    at {{messageObject.formatted_created}}</i>
                                </p>
                            </div>

                            <h1><strong>{{messageObject.message_title}}</strong></h1>

                            <div class="message-desc" v-html="messageObject.message"></div>

                            <div v-if="messageObject.files.length > 0">
                                <div v-for="file in messageObject.files" class="image-common">
                                    <files-type-display :file="file" type="normal"></files-type-display>
                                    <!-- <a :href="file.url" target="_blank"><img :src="file.url" alt="" class="image-resize"></a> -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <comments :i18n="i18n" :comments="messageObject.comments" type="message"></comments>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    .image-common {
        padding:10px;
        margin-bottom:20px;
        text-align:center;
        padding-left: 30px;
        box-sizing: border-box;
    }
    .image-resize {
        max-width:100%;
        max-height:100%;
    }
    .message-content {
        padding: 20px 60px;
    }
    .message-desc {
        padding-left: 30px;
        border-left: 3px solid #eaeaea;
    }
    .message-by {
        margin-top: -40px;
        margin-bottom: 30px;
    }
</style>

<script>
    import store from '../store';
    import Comments from './partials/CommentsComponent.vue';
    import FilesTypeDisplay from './partials/FilesTypeDisplay.vue';
    import ProjectNav from './partials/ProjectNavComponent.vue';
    export default {
        components: {
            Comments,
            FilesTypeDisplay,
            ProjectNav
        },
        data() {
            return {
                i18n: {},
                loading: false,
                localString: '',
                messageObject: ''
            }
        },
        computed: {
            isShowEdit: function() {
                var vm = this;
                return (vm.currentUser.roles[0] === 'administrator') ||
                        (vm.currentUser.data.ID === vm.messageObject.userID);
            }
        },
        methods: {
            fetchMessage: function() {
                var vm = this,
                    projectID = vm.$route.params.projectid;


                vm.loading = true;

                var data = {
                    action: 'fpm-get-message-details',
                    project_id: vm.$route.params.projectid,
                    message_id: vm.$route.params.messageid,
                    nonce: fpm.nonce,
                };

                jQuery.post( fpm.ajaxurl, data, function( resp ) {
                    vm.loading = false;
                    
                    if ( resp.success ) {
                        vm.messageObject = resp.data[0];
                    } else {
                        vm.$router.push({
                            path: `/?type=message&info=notfound`
                        });
                    }
                });
            },

            editMessage: function() {
                var vm = this,
                    todo,
                    data = {
                        action : 'fpm-create-user',
                        nonce : fpm.nonce,
                        user_name: vm.username,
                        email: vm.email,
                        project_id: vm.$route.params.projectid
                    };

                jQuery.post( fpm.ajaxurl, data, function( resp ) {
                    
                    if ( resp.success ) {
                        if ( !resp.data.user.ID ) {
                            return;
                        }
                        var userObj = {};
                        userObj.ID = resp.data.user.ID;
                        userObj.display_name = vm.username;

                        vm.users.push(userObj);
                        vm.username = '';
                        vm.email = '';

                    } else {
                        vm.message = resp.data;
                    }
                });
            },

            deleteMessage( messageObj ) {
                if (confirm("Are you sure ??")) {
                    var vm = this,
                        projectID = +messageObj.projectID,
                        data = {
                            action : 'fpm-delete-message',
                            nonce : fpm.nonce,
                            message_id: messageObj.ID,
                            project_id: messageObj.projectID,
                            user_name: messageObj.user_name,
                            user_id: messageObj.userID,
                            message_title: messageObj.message_title
                        };

                    jQuery.post( fpm.ajaxurl, data, function( resp ) {
                        if ( resp.success ) {

                            vm.$router.push({
                                path: `/projects/${projectID}/messages`
                            });

                        } else {

                        }
                    });
                }
            }
        },

        created() {
            var vm = this;
            store.setLocalization( 'fpm-get-single-message-local-data' ).then( function( data ) {
                vm.i18n = data;
            });

            vm.fetchMessage();
            vm.currentUser = fpm.currentUserInfo;
            // store.getLocalizeString().then(function(resp){
            //     vm.localString = resp.data.actions;
            // });
        },
    }
</script>