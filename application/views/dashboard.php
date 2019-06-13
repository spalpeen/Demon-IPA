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
                                    v-model="cron_colony"
                                    size="mini"
                                    placeholder="输入集群搜索"/>
                        </template>
                        <template slot-scope="scope">
                            <el-button
                                    size="mini"
                                    @click="handle_edit(scope.row,scope.$index)">编辑
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

    <el-dialog
            title="提示"
            :visible.sync="dialogVisible"
            width="30%"
    >
        <span>{{ list[select_index] }}</span>
        <el-input
                placeholder="请输入内容"
                v-model="input"
                :disabled="true">
        </el-input>
        <span slot="footer" class="dialog-footer">
    <el-button @click="dialogVisible = false">取 消</el-button>
    <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
  </span>
    </el-dialog>
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

</style>

<script>
    new Vue({
        el: '#app',
        data: function () {
            return {
                //列表数据
                list: [],

                // 每页多少条
                pageSize: 20,

                // 第几页
                pageNum: 1,

                //集群
                cron_colony: '',

                //弹窗
                dialogVisible: false,

                //list 索引
                select_index: -1,
            }
        },
        watch: {
            'cron_colony': function (val, old) {
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
            /**
             * 获取数据：列表信息
             */
            get_list() {
                let _this = this;
                axios.get('/admin/manager_job', {
                    cron_colony: this.cron_colony,
                    page_num: this.page_num,
                    page_size: this.page_size,
                })
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

            get_detail(item) {
                let form = new FormData();
                form.append('id', item.id);
                let _this = this;
                axios.post('/admin/manager_job/detail', form)
                    .then(function (response) {
                        // handle success
                        console.log(response)
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
                let _this = this;
                axios.post('/admin/manager_job/delete', form)
                    // handle success
                    .then(function (response) {
                        _this.list.splice(index,1)
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
            handle_edit(item,index) {
                this.dialogVisible = true;
                this.select_index = index;
                console.log(this.list[this.select_index])
            }
        },

        components: {

        }
    })
</script>

</html>