<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
</head>
<body>

<div id="app">
    <el-container style="height: 100vh; border: 1px solid #eee">
        <el-aside width="200px" style="background-color: rgb(238, 241, 246)">
            <el-menu :default-openeds="['1']">
                <el-submenu index="1">
                    <template slot="title"><i class="el-icon-menu"></i>首页</template>
                    <el-menu-item-group>
                        <el-menu-item index="1-1">任务</el-menu-item>
                        <el-menu-item index="1-2">日志</el-menu-item>
                    </el-menu-item-group>
<!--                    <el-menu-item-group title="分组2">-->
<!--                        <el-menu-item index="1-3">选项3</el-menu-item>-->
<!--                    </el-menu-item-group>-->
<!--                    <el-submenu index="1-4">-->
<!--                        <template slot="title">选项4</template>-->
<!--                        <el-menu-item index="1-4-1">选项4-1</el-menu-item>-->
<!--                    </el-submenu>-->
                </el-submenu>
            </el-menu>
        </el-aside>

        <el-container>

            <el-main>
                <el-table :data="list" style="width: 100%">
                    <el-table-column
                            label="创建时间"
                            prop="createtime">
                    </el-table-column>
                    <el-table-column
                            label="管理者"
                            prop="cron_manager">
                    </el-table-column>
                    <el-table-column
                            label="集群"
                            prop="cron_colony">
                    </el-table-column>
                    <el-table-column
                            label="任务名称"
                            prop="cron_name">
                    </el-table-column>
                    <el-table-column
                            label="任务规则"
                            prop="cron_rule">
                    </el-table-column>
                    <el-table-column
                            label="状态"
                            prop="status">
                    </el-table-column>
                    <el-table-column
                            align="right">
                        <template slot="header" slot-scope="scope">
                            <el-input
                                    v-model="search_cron_colony"
                                    size="mini"
                                    placeholder="输入集群搜索"/>
                        </template>
                        <template slot-scope="scope">
                            <el-button
                                    size="mini"
                                    @click="handle_edit(scope.row)">编辑
                            </el-button>
                            <el-button
                                    size="mini"
                                    type="danger"
                                    @click="handle_delete(scope.row,scope.$index)">删除
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </el-main>
        </el-container>
    </el-container>

    <!--    edit-dialog-->
    <el-dialog
            class="my-dialog"
            title="提示"
            :visible.sync="dialog_edit_visible"
            width="50%"
    >

        <el-input
                placeholder="创建时间"
                v-model="createtime"
                :disabled="true">
        </el-input>
        <el-input
                placeholder="管理者"
                v-model="cron_manager"
                :disabled="false">
        </el-input>
        <el-input
                placeholder="集群"
                v-model="cron_colony"
                :disabled="true">
        </el-input>
        <el-input
                placeholder="任务名称"
                v-model="cron_name"
                :disabled="true">
        </el-input>
        <el-input
                placeholder="任务规则"
                v-model="cron_rule"
                :disabled="false">
        </el-input>
        <el-select v-model="status">
            <el-option
                    v-for="item in status_options"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
            </el-option>
        </el-select>

        <el-input type="textarea" v-model="cron_execution" :rows="5" placeholder="执行命令">
        </el-input>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialog_edit_visible = false">取 消</el-button>
            <el-button type="primary" @click="handle_edit_post">确 定</el-button>
        </span>
    </el-dialog>

    <!--    add_dialog-->
    <el-dialog
            class="my-dialog-add"
            title="提示"
            :visible.sync="dialog_add_visible"
            width="50%"
    >
        <el-input
                placeholder="管理者"
                v-model="add_data.cron_manager"
                :disabled="false">
        </el-input>
        <el-input
                placeholder="集群"
                v-model="add_data.cron_colony"
                :disabled="false">
        </el-input>
        <el-input
                placeholder="任务名称"
                v-model="add_data.cron_name"
                :disabled="false">
        </el-input>
        <el-input
                placeholder="任务规则"
                v-model="add_data.cron_rule"
                :disabled="false">
        </el-input>
        <el-select v-model="add_data.status">
            <el-option
                    v-for="item in status_options"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value">
            </el-option>
        </el-select>

        <el-input type="textarea" v-model="add_data.cron_execution" :rows="5" placeholder="执行命令">
        </el-input>
        <span slot="footer" class="dialog-footer">
            <el-button @click="dialog_add_visible = false">取 消</el-button>
            <el-button type="primary" @click="handle_add_post">确 定</el-button>
        </span>
    </el-dialog>
    <el-button type="primary" style="position: fixed;bottom: 80px;right: 40px;" icon="el-icon-edit" circle
               @click="handle_add"></el-button>

</div>
</body>
<!-- import Vue before Element -->
<script src="https://unpkg.com/vue/dist/vue.js"></script>
<!-- import JavaScript -->
<script src="https://unpkg.com/element-ui/lib/index.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

<style>
    .el-header {
        background-color: #B3C0D1;
        color: #333;
        line-height: 60px;
    }

    .el-aside {
        color: #333;
    }

    .my-dialog .el-input {
        margin-bottom: 10px;
    }

    .my-dialog-add .el-input {
        margin-bottom: 10px;
    }

</style>

<script>
    new Vue({
        el: '#app',
        data: function () {
            return {
                //列表数据
                list: [],

                // 每页多少条
                page_size: 20,

                // 第几页
                page_num: 1,

                //id
                id: '',

                //集群
                cron_colony: '',

                //创建时间
                createtime: '',

                //管理者
                cron_manager: '',

                //任务名称
                cron_name: '',

                //任务执行语句
                cron_execution: '',

                //任务规则
                cron_rule: '',

                //状态
                status: '1',

                search_cron_colony: '',

                //修改弹窗
                dialog_edit_visible: false,

                //添加弹窗
                dialog_add_visible: false,

                //list 索引
                select_index: -1,

                status_options: [{
                    value: '1',
                    label: '无效'
                }, {
                    value: '2',
                    label: '有效'
                }],
                add_data: {
                    cron_colony: '',
                    cron_manager: '',
                    cron_name: '',
                    cron_execution: '',
                    cron_rule: '',
                    status: '1',
                },
            }
        },
        watch: {
            'search_cron_colony': function (val, old) {
                if (val) {
                    this.get_list();
                }
            }
        },
        created() {
            this.get_list()
        },
        mounted() {
        },
        methods: {
            handle_click(index) {
                console.log(index)
                switch (index) {
                    case '1-2':
                        location.href = '/admin/manager_job';
                        break;
                }
            },
            /**
             * 获取数据：列表信息
             */
            get_list() {
                let _this = this;
                axios.get('/admin/manager_job?cronColony='+ _this.search_cron_colony + '&pageNum=' + _this.page_num + '&pageSize=' + _this.page_size)
                    .then(function (response) {
                        // handle success
                        _this.list = response.data.response
                        console.log(_this.list)
                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                    })
                    .finally(function () {
                        // always executed
                    });
            },

            handle_delete(item, index) {
                let form = new FormData();
                form.append('id', item.id);
                form.append('status', 3);
                let _this = this;
                axios.post('/admin/manager_job/delete', form)
                // handle success
                    .then(function (response) {
                        _this.list.splice(index, 1);
                        _this.$message({
                            message: '删除成功',
                            center: true
                        });

                    })
                    .catch(function (error) {
                        // handle error
                        console.log(error);
                    })
                    .finally(function () {
                        // always executed
                    });

            },
            handle_edit(item, index) {
                this.dialog_edit_visible = true;
                this.id = this.list[index].id;
                this.createtime = this.list[index].createtime;
                this.cron_manager = this.list[index].cron_manager;
                this.cron_colony = this.list[index].cron_colony;
                this.cron_name = this.list[index].cron_name;
                this.cron_rule = this.list[index].cron_rule;
                this.status = this.list[index].status;
                this.cron_execution = this.list[index].cron_execution;
            },
            handle_add() {
                this.dialog_add_visible = true;
            },

            handle_add_post() {
                let _this = this;
                let form = new FormData();
                form.append('status', _this.add_data.status);
                form.append('cron_manager', _this.add_data.cron_manager);
                form.append('cron_colony', _this.add_data.cron_colony);
                form.append('cron_name', _this.add_data.cron_name);
                form.append('cron_rule', _this.add_data.cron_rule);
                form.append('cron_execution', _this.add_data.cron_execution);
                axios.post('/admin/manager_job/add', form)
                // handle success
                    .then(function (response) {
                        _this.dialog_add_visible = false;
                        _this.$message({
                            message: response.data.message,
                            center: true
                        });

                    })
                    .catch(function (error) {
                        // handle error
                        _this.$message({
                            message: '添加失败',
                            center: true
                        });
                        console.log(error);
                    })
                    .finally(function () {
                        // always executed
                    });
            },
            handle_edit_post() {
                let _this = this;
                let form = new FormData();
                form.append('id', _this.id);
                form.append('status', _this.status);
                form.append('cron_manager', _this.cron_manager);
                form.append('cron_colony', _this.cron_colony);
                form.append('cron_name', _this.cron_name);
                form.append('cron_rule', _this.cron_rule);
                form.append('cron_execution', _this.cron_execution);
                axios.post('/admin/manager_job/edit', form)
                // handle success
                    .then(function (response) {
                        _this.dialog_edit_visible = false;
                        console.log(response.data.code)
                        _this.$message({
                            message: response.data.message,
                            center: true
                        });

                    })
                    .catch(function (error) {
                        // handle error
                        _this.$message({
                            message: '修改失败',
                            center: true
                        });
                        console.log(error);
                    })
                    .finally(function () {

                    });
        },

        components: {}
    })
</script>

</html>