/**
 * Created by lk on 17/6/4.
 */
import axios from "../utils/axios";

// 获取列表
export function moduleList(query) {
    return axios({
        url: "/admin/module/index",
        method: "get",
        params: query
    });
}

// 保存
export function moduleSave(data, formName, method = "post") {
    let url = formName !== "edit" ? "/admin/module/add" : "/admin/module/edit";
    return axios({
        url: url,
        method: method,
        data: data
    });
}

// 删除
export function moduleDalete(data) {
    return axios({
        url: "/admin/module/delete",
        method: "post",
        data: data
    });
}
