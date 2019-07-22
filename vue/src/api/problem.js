/**
 * Created by lk on 17/6/4.
 */
import axios from "../utils/axios";

// 获取列表
export function problemList(query) {
    return axios({
        url: "/admin/problem/index",
        method: "get",
        params: query
    });
}

// 保存
export function problemSave(data, formName, method = "post") {
    let url = formName === "add" ? "/admin/problem/add" : "/admin/problem/edit";
    return axios({
        url: url,
        method: method,
        data: data
    });
}

// 删除
export function problemDelete(data) {
    return axios({
        url: "/admin/problem/delete",
        method: "post",
        data: data
    });
}
