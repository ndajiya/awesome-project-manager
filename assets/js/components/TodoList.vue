<template>
    <div>
        <div class="container">
            <project-nav>
                <span><i class="fa fa-angle-right"></i></span>
                <router-link :to="'/projects/' + $route.params.projectid + '/todolists'" class="link-style t-d-none">
                    To-Dos
                </router-link>
            </project-nav>
            
            <div class="lists border-for-nav">
                <div class="row">
                    <div class="col-12">

                        <div class="text-center" v-if="loading">
                            <i class="fa fa-refresh fa-spin fa-3x fa-fw" aria-hidden="true"></i>
                        </div>

                        <div v-if="list && !loading">
                            <ul>
                                <list :i18n="i18n" is-single="true" :list="list" :sindex="0"></list>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
    .lists {
        background: #fff;
        padding: 15px 25px;
        border-radius: 5px;
    }
    .inline-block {
        display: inline-block;
    }
    .t-d-none {
        text-decoration: none;
    }
</style>

<script>
    import store from '../store';
    import List from './partials/ListComponent.vue';
    import Comments from './partials/CommentsComponent.vue';
    import ProjectNav from './partials/ProjectNavComponent.vue';
    export default {
        components: {
            'list': List,
            'comments': Comments,
            ProjectNav
        },

        data() {
            return {
                i18n: {},
                list: {},
                loading: false,
                sindex: 0,
                project: ''
            }
        },

        methods: {
            fetchListDetails: function() {
                var vm = this;
                vm.loading = true;

                var data = {
                    action: 'fpm-get-list-details',
                    project_id: vm.$route.params.projectid,
                    list_id: vm.$route.params.listid,
                    nonce: fpm.nonce,
                };

                jQuery.post( fpm.ajaxurl, data, function( resp ) {
                    vm.loading = false;
                    if ( resp.success ) {
                        vm.list = resp.data[0];
                        vm.project = resp.data[0].project_info;
                    } else {
                        vm.$router.push({
                            path: `/?type=todolist&info=notfound`
                        });
                    }
                });
            }
        },

        created() {
            var vm = this;

            store.setLocalization( 'fpm-get-todo-lists-local-data' ).then( function( data ) {
                vm.i18n = data;
            });
            vm.fetchListDetails();
        }
    }
</script>