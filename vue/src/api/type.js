/**
 * Created by lk on 17/6/4.
 */
import axios from "../utils/axios";

// 获取列表
export function typeList(query) {
    return axios({
        url: "/admin/type/index",
        method: "get",
        params: query
    });
}

// 保存
export function typeSave(data, formName, method = "post") {
    let url = formName === "add" ? "/admin/type/add" : "/admin/type/edit";
    return axios({
        url: url,
        method: method,
        data: data
    });
}

// 删除
export function typeDelete(data) {
    return axios({
        url: "/admin/type/delete",
        method: "post",
        data: data
    });
}
