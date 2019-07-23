<template>
    <div>
        <el-form :inline="true" :model="query" class="query-form" size="mini">
            <el-form-item class="query-form-item">
                <el-select v-model="query.timeType" placeholder="时间类型">
                    <el-option label="创建时间" value="0"></el-option>
                    <el-option label="修改时间" value="1"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-date-picker
                    v-model="query.time"
                    type="daterange"
                    align="right"
                    unlink-panels
                    range-separator="至"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期"
                    :picker-options="pickerOptions"
                    format="yyyy 年 MM 月 dd 日"
                    value-format="yyyy-MM-dd"
                >
                </el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-select v-model="query.type_id" placeholder="问题类型">
                    <el-option
                        v-for="item in typeList"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id"
                    >
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-cascader
                    v-model="selectedOptions"
                    :options="moduleList"
                    :props="optionProps"
                    @change="editModuleOption"
                    placeholder="问题模块"
                ></el-cascader>
            </el-form-item>
            <el-form-item class="query-form-item">
                <el-select v-model="query.status" placeholder="状态">
                    <el-option label="全部" value=""></el-option>
                    <el-option label="待处理" value="0"></el-option>
                    <el-option label="已解决" value="1"></el-option>
                    <el-option label="未解决" value="2"></el-option>
                </el-select>
            </el-form-item>

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
                        type="primary"
                        @click.native="handleForm(null, null)"
                        >新增</el-button
                    >
                     <el-button
                        type="primary"
                        >{{total}}</el-button
                    >
                </el-button-group>
            </el-form-item>
        </el-form>
        <el-table v-loading="loading" :data="list" style="width: 100%;">
            <el-table-column type="expand">
                <template slot-scope="props">
                    <el-form label-position="left" inline class="demo-table-expand"  v-if="props.row.logs.length">
                        <el-form-item :label="v.nickname || '匿名者'"  v-for="(v,k) in props.row.logs" :key="k">
                            <span>{{ v.content }}</span>
                        </el-form-item>
                    </el-form>
                    <el-alert
                        v-else
                        title="该问题暂时没有日志"
                        type="warning"
                        :closable="false">
                    </el-alert>
                    
                </template>
                
            </el-table-column>
            <el-table-column label="编号" type="index"> </el-table-column>
            <el-table-column label="问题内容" prop="content"> </el-table-column>
            <el-table-column label="优先级" sortable prop="weigh">
            </el-table-column>
            <el-table-column label="严重程度" sortable prop="serious">
            </el-table-column>
            <el-table-column label="提出部门" prop="department">
            </el-table-column>
            <el-table-column label="提出人" prop="nickname"> </el-table-column>
            <el-table-column label="状态">
                <template slot-scope="scope">
                    <el-tag :type="scope.row.status | statusFilterType" @click="handleStatusForm(scope.$index, scope.row)">{{
                        scope.row.status | statusFilterName
                    }}</el-tag>
                </template>
            </el-table-column>
            <el-table-column label="创建时间" prop="create_time">
            </el-table-column>
            <el-table-column label="修改时间" prop="update_time">
            </el-table-column>
            <el-table-column label="操作" fixed="right" width="200">
                <template slot-scope="scope">
                    <el-button
                        type="text"
                        size="small"
                        @click.native="handleLogForm(scope.$index, scope.row)"
                        >日志</el-button
                    >
                    <el-button
                        v-if="!scope.row.logs.length"
                        type="text"
                        size="small"
                        @click.native="handleForm(scope.$index, scope.row)"
                        >编辑</el-button
                    >
                    <el-button
                        v-if="!scope.row.logs.length"
                        type="text"
                        size="small"
                        @click.native="handleDel(scope.$index, scope.row)"
                        :loading="deleteLoading"
                        >删除</el-button
                    >
                    <el-button
                        v-if="scope.row.logs.length"
                        type="text"
                        size="small"
                        @click.native="handleForm(scope.$index, scope.row)"
                        >查看</el-button
                    >
                   
                </template>
            </el-table-column>
        </el-table>
        
        <el-pagination
            :page-size="query.limit"
            @current-change="handleCurrentChange"
            layout="prev, pager, next"
            :total="total"
            :current-page="query.page"
        >
        </el-pagination>

        <!--表单-->
        <el-dialog
            :title="formMap[formName]"
            :visible.sync="formVisible"
            :before-close="hideForm"
            width="85%"
            top="5vh"
        >
            <el-form :model="formData" :rules="formRules" ref="dataForm" :disabled="checkData">
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="问题类型" prop="type_id">
                            <el-select
                                v-model="formData.type_id"
                                placeholder="请选择问题类型"
                            >
                                <el-option
                                    v-for="item in typeList"
                                    :key="item.id"
                                    :label="item.name"
                                    :value="item.id"
                                >
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="优先级别" prop="weigh">
                            <el-input-number
                                v-model="formData.weigh"
                                :min="0"
                            ></el-input-number>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <el-form-item label="问题模块" prop="module_path">
                            <el-cascader
                                v-model="selectedOptions"
                                :options="moduleList"
                                :props="optionProps"
                                @change="editOption"
                            ></el-cascader>
                        </el-form-item>
                    </el-col>
                    <el-col :span="12">
                        <el-form-item label="严重程度" prop="serious">
                            <el-input-number
                                v-model="formData.serious"
                                :min="0"
                            ></el-input-number>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-form-item label="问题内容" prop="content">
                    <el-input
                        v-model="formData.content"
                        auto-complete="off"
                    ></el-input>
                </el-form-item>
                <el-form-item label="改进方案" prop="programme">
                    <el-input
                        v-model="formData.programme"
                        auto-complete="off"
                    ></el-input>
                </el-form-item>
                <el-form-item label="问题链接" prop="link">
                    <el-input
                        v-model="formData.link"
                        auto-complete="off"
                    ></el-input>
                </el-form-item>
                <el-form-item label="备注">
                    <el-input
                        type="textarea"
                        v-model="formData.remark"
                    ></el-input>
                </el-form-item>
                <el-form-item>
                    <el-upload
                        :action="imgUrl"
                        list-type="picture-card"
                        :on-preview="handlePictureCardPreview"
                        :on-remove="handleRemove"
                        :on-success="handleSuccess"
                        :file-list="fileList"
                    >
                        <i class="el-icon-plus"></i>
                    </el-upload>
                </el-form-item>
                <el-form-item label="状态" prop="status">
                    <el-radio-group v-model="formData.status">
                        <el-radio :label="0">待处理</el-radio>
                        <el-radio :label="1">已解决</el-radio>
                        <el-radio :label="2">未解决</el-radio>
                    </el-radio-group>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="hideForm">取消</el-button>
                <el-button
                    :disabled="checkData"
                    type="primary"
                    @click.native="formSubmit()"
                    :loading="formLoading"
                    >提交</el-button
                >
            </div>
        </el-dialog>
        <!--日志表单-->
        <el-dialog
            title="添加问题日志"
            :visible.sync="logFormVisible"
            :before-close="hideLogForm"
            width="56%"
            top="5vh"
        >
            <el-form :model="formData" :rules="formRules" ref="dataForm" >
                <el-form-item label="日志内容">
                    <el-input
                        v-model="log.content"
                        auto-complete="off"
                    ></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="hideLogForm">取消</el-button>
                <el-button
                    type="primary"
                    @click.native="logFormSubmit()"
                    :loading="formLoading"
                    >提交</el-button
                >
            </div>
        </el-dialog>
        <!-- 图片显示 -->
        <el-dialog :visible.sync="dialogVisible">
            <img width="100%" :src="dialogImageUrl" alt="" />
        </el-dialog>
        <!-- 状态表示 -->
        <el-dialog :visible.sync="statusFormVisible" title="修改状态">
            <el-radio-group label="问题状态" v-model="statusForm.status">
                <el-radio :label="0" border>待处理</el-radio>
                <el-radio :label="1" border>已解决</el-radio>
                <el-radio :label="2" border>未解决</el-radio>
            </el-radio-group>
            <div slot="footer" class="dialog-footer">
                <el-button @click.native="hideStatusForm">取消</el-button>
                <el-button
                    type="primary"
                    @click.native="statusFormSubmit()"
                    :loading="formLoading"
                    >提交</el-button
                >
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { problemList, problemSave, problemDelete, problemLog } from "../../api/problem";
import { typeList } from "../../api/type";
import { moduleList } from "../../api/module";
import { remove } from "../../api/file";
import { IMG_BASE_URL } from "../../config/app";
const formJson = {
    id: "",
    content: "",
    programme: "",
    path: "-",
    type_id: "",
    status: 0,
    link: "",
    remark: "",
    weigh: 0,
    serious: 0
};
export default {
    data() {
        return {
            query: {
                status: "",
                path: "",
                type_id: "",
                page: 1,
                limit: 10,
                time: [],
                timeType: "0"
            },
            list: [],
            total: 0,
            loading: true,
            authList: [],
            defaultProps: {
                children: "children",
                label: "title"
            },
            authLoading: false,
            authFormVisible: false,
            authFormVisible: false,
            authFormData: {
                role_id: "",
                auth_rules: []
            },
            authDefaultCheckedKeys: [],
            index: null,
            formName: null,
            formMap: {
                add: "新增",
                edit: "编辑"
            },
            formLoading: false,
            formVisible: false,
            logFormVisible: false,
            formData: formJson,
            formRules: {
                name: [
                    { required: true, message: "请输入名称", trigger: "blur" }
                ],
                status: [
                    { required: true, message: "请选择状态", trigger: "change" }
                ]
            },
            deleteLoading: false,
            typeList: [],
            moduleList: [],
            optionProps: {
                value: "id",
                label: "name",
                checkStrictly: true
            },
            selectedOptions: [],
            fileList: [],
            dialogImageUrl: "",
            dialogVisible: false,
            pickerOptions: {
                shortcuts: [
                    {
                        text: "最近一周",
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(
                                start.getTime() - 3600 * 1000 * 24 * 7
                            );
                            picker.$emit("pick", [start, end]);
                        }
                    },
                    {
                        text: "最近一个月",
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(
                                start.getTime() - 3600 * 1000 * 24 * 30
                            );
                            picker.$emit("pick", [start, end]);
                        }
                    },
                    {
                        text: "最近三个月",
                        onClick(picker) {
                            const end = new Date();
                            const start = new Date();
                            start.setTime(
                                start.getTime() - 3600 * 1000 * 24 * 90
                            );
                            picker.$emit("pick", [start, end]);
                        }
                    }
                ]
            },
            imgUrl: IMG_BASE_URL + "admin/upload/save",
            log: {
                problem_id: "",
                content: "",
            },
            checkData: false,
            statusForm: {
                id: "",
                status: ""
            },
            statusFormVisible: false
        };
    },
    methods: {
        onReset() {
            this.$router.push({
                path: ""
            });
            this.query = {
                status: "",
                path: "",
                type_id: "",
                page: 1,
                limit: 10,
            };

            this.selectedOptions = [];
            this.getList();
        },
        onSubmit() {
            this.getList();
        },
        handleCurrentChange(val) {
            this.query.page = val;
            this.getList();
        },
        getList() {
            this.loading = true;
            problemList(this.query)
                .then(response => {
                    this.loading = false;
                    this.list = response.data.list || [];
                    this.total = response.data.total || 0;
                })
                .catch(() => {
                    this.loading = false;
                    this.list = [];
                    this.total = 0;
                });
        },
        // 获取问题类型列表
        getTypeList() {
            typeList(this.query)
                .then(response => {
                    this.loading = false;
                    this.typeList = response.data.list || [];
                })
                .catch(() => {
                    this.loading = false;
                    this.typeList = [];
                });
        },
        // 获取问题模块
        getModuleList() {
            moduleList(this.query)
                .then(response => {
                    this.loading = false;
                    this.moduleList = response.data.list || [];
                })
                .catch(() => {
                    this.loading = false;
                    this.moduleList = [];
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
            // 清空文件列表
            this.fileList = [];
            return true;
        },
        // 显示表单
        handleForm(index, row) {
            this.formVisible = true;
            this.formData = JSON.parse(JSON.stringify(formJson));
            if (row !== null) {
                this.formData = Object.assign({}, row);
            }
            this.formName = "add";

            
            let path = [];
            if (index !== null) {
                this.index = index;
                this.formName = "edit";
                this.checkData = row.logs.length ? true : false;
                if (row.images) {
                    if (typeof row.images === "string") {
                        let arr = [];
                        let images = row.images.split(",");
                        images.forEach(element => {
                            arr.push({ url: element });
                        });
                        this.fileList = arr;
                    } else {
                        this.fileList = row.images;
                    }
                } else {
                    this.fileList = [];
                }
                // 模块
                let pathList = row.path.split("-");
                pathList.forEach(element => {
                    if (element) {
                        path.push(Number(element));
                    }
                });
            }
            this.selectedOptions = path;        
        },
        // 显示日志表单
        handleLogForm(index, row) {
            this.logFormVisible = true;
            this.log.problem_id = row.id;
            this.index = index;
        },
        // 隐藏日志表单
        hideLogForm() {
            // 更改值
            this.logFormVisible = !this.logFormVisible;
            // 清空文件列表
            return true;
        },
        // 日志表单提交
        logFormSubmit(){
            this.formLoading = true;
            problemLog(this.log) 
                .then(response => {
                    this.formLoading = false;
                    if (response.code) {
                        this.$message.error(response.message);
                        return false;
                    }
                    this.$message.success("操作成功");
                    this.logFormVisible = false;
                    this.list[this.index].logs.unshift(response.data);
                    // 刷新表单
                    this.log = {};
                })
        },
        // 显示状态表单
        handleStatusForm(index, row) {
            this.statusFormVisible = true;
            this.statusForm = {id: row.id, status: row.status};
            this.index = index;
        },
        // 隐藏状态表单
        hideStatusForm() {
            // 更改值
            this.statusFormVisible = !this.statusFormVisible;
            // 清空文件列表
            return true;
        },
        // 状态表单提交
        statusFormSubmit(){
            this.formLoading = true;
            let data = Object.assign({}, this.statusForm);
            problemSave(data, 'edit') 
                .then(response => {
                    this.formLoading = false;
                    if (response.code) {
                        this.$message.error(response.message);
                        return false;
                    }
                    this.$message.success("操作成功");
                    this.statusFormVisible = false;
                    this.list[this.index].status = data.status;
                    // 刷新表单
                    this.statusForm = {};
                })
        },
        editOption() {
            let option = this.selectedOptions;
            if (option.length > 0) {
                this.formData.path = "-" + option.join("-") + "-";
                // this.formData.pid = option[option.length - 1];
            }
        },
        editModuleOption(d) {
            if (d.length > 0) {
                this.query.path = "-" + d.join("-") + "-";
                // this.formData.pid = option[option.length - 1];
            }
        },
        formSubmit() {
            this.$refs["dataForm"].validate(valid => {
                if (valid) {
                    this.formLoading = true;
                    let data = Object.assign({}, this.formData);
                    data.images = this.fileList;
                    // return false;
                    problemSave(data, this.formName)
                        .then(response => {
                            this.formLoading = false;
                            if (response.code) {
                                this.$message.error(response.message);
                                return false;
                            }
                            this.$message.success("操作成功");
                            this.formVisible = false;
                            if (this.formName === "add") {
                                // 向头部添加数据
                                if (response.data && response.data.id) {
                                    data.id = response.data.id;
                                    // this.list.unshift(data);
                                    this.list.push(response.data);
                                    this.total++;
                                }
                            } else {
                                this.list.splice(this.index, 1, data);
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
        handleDel(index, row) {
            if (row.id) {
                this.$confirm("确认删除该记录吗?", "提示", {
                    type: "warning"
                })
                    .then(() => {
                        this.deleteLoading = true;
                        let para = { id: row.id };
                        problemDelete(para)
                            .then(response => {
                                this.deleteLoading = false;
                                if (response.code) {
                                    this.$message.error(response.message);
                                    return false;
                                }
                                this.$message.success("删除成功");
                                // 刷新数据
                                this.list.splice(index, 1);
                                this.total--;
                            })
                            .catch(() => {
                                this.deleteLoading = false;
                            });
                    })
                    .catch(() => {
                        this.$message.info("取消删除");
                    });
            }
        },
        handleRemove(file, fileList) {
            // console.log(file, fileList);
            this.fileList = fileList;
            remove(file)
        },
        handleSuccess(file) {
            this.fileList.push({ url: file.data.src });
        },
        handlePictureCardPreview(file) {
            this.dialogImageUrl = file.url;
            this.dialogVisible = true;
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
                0: "待处理",
                1: "已解决",
                2: "未解决"
            };
            return statusMap[status];
        }
    },
    mounted() {},
    created() {
        // 加载表格数据
        this.getList();
        this.getTypeList();
        this.getModuleList();
    }
};
</script>

<style>
  .demo-table-expand {
    font-size: 0;
  }
  .demo-table-expand label {
    width: 90px;
    color: #99a9bf;
  }
  .demo-table-expand .el-form-item {
    margin-right: 0;
    margin-bottom: 0;
    width: 100%;
  }

  /* 下拉菜单 */
  .el-dropdown-link {
    cursor: pointer;
    color: #409EFF;
  }
  .el-icon-arrow-down {
    font-size: 12px;
  }
</style>
