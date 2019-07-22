/**
 * Created by lk on 17/6/4.
 */
import axios from "../utils/axios";

// // 上传文件
// export function upload(url, formdata) {
//     return axios({
//         url: '/admin/file/upload',
//         method: "post",
//         data: formdata
//     });
// }

// 删除
export function remove(data) {
    return axios({
        url: "/admin/upload/delete",
        method: "post",
        data: data
    });
}
