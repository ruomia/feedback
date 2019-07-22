<template>
    <div>
        <el-form :inline="true" :model="query" class="query-form" size="mini">
            <el-form-item class="query-form-item">
                <el-input
                    v-model="query.name"
                    placeholder="分类名称"
                ></el-input>
            </el-form-item>
            <!-- <el-form-item class="query-form-item">
        <el-select v-model="query.status" placeholder="状态">
          <el-option label="全部" value></el-option>
          <el-option label="禁用" value="0"></el-option>
          <el-option label="正常" value="1"></el-option>
        </el-select>
      </el-form-item> -->

            <el-form-item>
                <el-button-group>
                    <el-button
                        type="primary"
                        icon="el-icon-refresh"
                        @click="onReset"
                    ></el-button>
                    <el-button type="primary" icon="search" @click="onSubmit"
                        >查询</el-button
                    >
                    <el-button
                        v-if="addBtnIsDisplay"
                        type="primary"
                        @click.native="handleForm(null, null)"
                        >新增</el-button
                    >
                </el-button-group>
            </el-form-item>
        </el-form>
        
    <el-tree
      :data="mergeList"
      :props="defaultProps"
      node-key="id"
      default-expand-all
      :expand-on-click-node="false"
      :render-content="renderContent"
    ></el-tree>
      

        <!--表单界面-->
        <el-dialog
            :title="formMap[formName]"
            :visible.sync="formVisible"
            :before-close="hideForm"
            width="65%"
            top="5vh"
        >
            <el-form :model="formData" :rules="formRules" ref="dataForm">
                <el-form-item label="上级分类" prop="pid">
                    <el-cascader
                        v-model="selectedOptions"
                        :options="mergeList"
                        :props="optionProps"
                        :change-on-select="true"
                        @change="editOption"
                    ></el-cascader>
                </el-form-item>
                <el-form-item label="分类名称" prop="name">
                    <el-input
                        type
                        v-model="formData.name"
                        auto-complete="off"
                    ></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="hideForm">取消</el-button>
                <el-button
                    type="primary"
                    @click.native="formSubmit()"
                    :loading="formLoading"
                    >提交</el-button
                >
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { moduleList, moduleSave, moduleDalete } from "../../api/module";
const formJson = {
    id: "",
    pid: 0,
    name: "",
    path: "-"
};
export default {
    data() {
        return {
            query: {
                name: "",
                status: ""
            },
            mergeList: [],
            node: null,
            defaultProps: {
                children: "children",
                label: "name"
            },
            treeList: [],
            loading: true,
            index: null,
            formName: null,
            formMap: {
                add: "新增",
                edit: "编辑"
            },
            formLoading: false,
            formVisible: false,
            formData: formJson,
            formRules: {
                name: [
                    { required: true, message: "请输入模块名", trigger: "blur" }
                ]
            },
            pidData: {},
            deleteLoading: false,
            optionProps: {
                value: "id",
                label: "name"
            },
            selectedOptions: [],
            addBtnIsDisplay: true,
            editBtnIsDisplay: true,
            delBtnIsDisplay: true
        };
    },
    methods: {
        /*eslint-disable */
        btnIsDisplay () {
           let authRules = this.$store.getters.authRules;
            let result = authRules.findIndex(value => value === 'admin') 
            if(result === 0) {
                return ;
            }
            result = authRules.findIndex(value => value === 'module/add')
            if(result < 0) {
                this.addBtnIsDisplay = false;
            } 
            result = authRules.findIndex(value => value === 'module/edit')
            if(result < 0) {
                this.editBtnIsDisplay = false;
            } 
            result = authRules.findIndex(value => value === 'module/del')
            if(result < 0) {
                this.delBtnIsDisplay = false;
            } 
        },
        renderContent (h, { node, data, store }) {
       
            return (
          <span style="flex: 1; display: flex; align-items: center; justify-content: space-between; font-size: 14px; padding-right: 8px;">
            <span>{node.label}</span>
            <span>
                {this.addBtnIsDisplay ? <el-button  style="font-size: 12px;" type="text" on-click={ () => this.handleForm(node, data, 'add') }>添加子模块</el-button> :"" }
                {this.addBtnIsDisplay ? <el-button style="font-size: 12px;" type="text" on-click={ () => this.handleForm(node, data, 'edit') }>编辑</el-button> :""}
                {this.addBtnIsDisplay ?  <el-button style="font-size: 12px;" type="text" on-click={ () => this.handleDel(node, data) }>删除</el-button> :"" }
            </span>
          </span>);
        },
        onReset() {
            this.$router.push({
                path: ""
            });
            this.query = {
                name: "",
                status: ""
            };
            this.getList();
        },
        onSubmit() {
            this.getList();
        },
        getList() {
            this.loading = true;
            moduleList(this.query)
                .then(response => {
                    this.loading = false;
                    this.mergeList = response.data.list || [];
                })
                .catch(() => {
                    this.loading = false;
                    this.mergeList = [];
                });
        },
        // 刷新表单
        resetForm() {
            if (this.$refs["dataForm"]) {
                // 清空验证信息表单
                this.$refs["dataForm"].clearValidate();
                // 刷新表单
                this.$refs["dataForm"].resetFields();
            }
        },
        // 隐藏表单
        hideForm() {
            // 更改值
            this.formVisible = !this.formVisible;
            return true;
        },
        // 显示表单
        handleForm(node, data, formName) {
            this.formVisible = true;
            this.pidData = data || null;
            formJson.pid = (data && parseInt(data.id)) || "";
            this.formData = JSON.parse(JSON.stringify(formJson));
            if (formName === "edit") {
                this.formData = Object.assign({}, data);
                this.node = node;
            }
            // console.log(data)
            this.formData.pid = !this.formData.pid ? "" : this.formData.pid;
            this.formName = formName;
            if (data && data.id) {
                this.index = this.mergeList.findIndex(d => d.id === data.id);
            }
            // let path = data.path.split('-');
            let path = [];
            // 先转换成数组
            if(data) {
                let pathList = data.path.split('-');
                pathList.forEach(element => {
                    if(element) {
                        path.push(Number(element));
                    }
                });
                if(formName === 'add') {
                    // 如果是添加模块，在path列表中添加当前记录的id
                    path.push(Number(data.id));
                    // this.formData.path = data.path + '-' + data.id;
                    this.formData.path = path.join('-');
                }
           
            }
            this.selectedOptions = path;
        },
        editOption(d) {
            let option = this.selectedOptions;
            if(option.length > 0) {
                this.formData.path = option.join("-");
                this.formData.pid = option[option.length - 1];
            }          
        },
        formSubmit() {
            this.$refs["dataForm"].validate(valid => {
                if (valid) {
                this.formLoading = true;
                let data = Object.assign({}, this.formData);
                moduleSave(data, this.formName)
                    .then(response => {
                        this.formLoading = false;
                        if (response.code) {
                            this.$message.error(response.message);
                            return false;
                        }
                        this.$message.success("操作成功");
                        this.formVisible = false;
                        if (this.formName !== "edit") {
                            if (response.data && response.data.id) {
                                data.id = response.data.id;
                                if (this.pidData) {
                                    if (!this.pidData.children) {
                                        this.$set(this.pidData, "children", []);
                                    }
                                    this.pidData.children.push(data);
                                } else {
                                    this.mergeList.push(data);
                                }
                            }
                        } else {

                            const parent = this.node.parent;
                            const children = parent.data.children || parent.data;
                            const index = children.findIndex(d => d.id === data.id);
                            children.splice(index, 1, data);

                        }
                        // 刷新表单
                        this.resetForm();
                    })
                    .catch(() => {
                        this.formLoading = false;
                    });
                }
            });
        },
        // 删除
        handleDel(node, data) {
            if (data.children && data.children.length > 0) {
                this.$alert("请先删除子节点", "提示", {
                    confirmButtonText: "确定"
                });
                return false;
            }
            if (data.id) {
                this.$confirm("确认删除该记录吗?", "提示", {type: "warning"})
                .then(() => {
                    this.deleteLoading = true;
                    let para = { id: data.id };
                    moduleDalete(para)
                    .then(response => {
                        this.deleteLoading = false;
                        if (response.code) {
                        this.$message.error(response.message);
                        return false;
                        }
                        this.$message.success("删除成功");
                        // 刷新数据
                        const parent = node.parent;
                        const children = parent.data.children || parent.data;
                        const index = children.findIndex(d => d.id === data.id);
                        children.splice(index, 1);
                    })
                    .catch(() => {
                        this.deleteLoading = false;
                    });
                })
                .catch(() => {
                    this.$message.info("取消删除");
                });
            }
        }
  },
  filters: {
        statusFilterType(status) {
            const statusMap = {
                0: "gray",
                1: "success"
            };
            return statusMap[status];
        },
        statusFilterName(status) {
            const statusMap = {
                0: "禁用",
                1: "正常"
            };
            return statusMap[status];
        }
    },
    mounted() {},
    created() {
        // 加载表格数据
        this.getList();
        // 加载树形结构
        // this.getTreeList();
        // 判断权限
        this.btnIsDisplay();
    }
};
</script>

<style type="text/scss" lang="scss">
</style>
