<?php
class Scalr_UI_Controller_Admin_Users extends Scalr_UI_Controller
{
    const CALL_PARAM_NAME = 'userId';

    public function hasAccess()
    {
        return $this->user && ($this->user->getType() == Scalr_Account_User::TYPE_SCALR_ADMIN);
    }

    public function defaultAction()
    {
        $this->viewAction();
    }

    public function viewAction()
    {
        $this->response->page('ui/admin/users/view.js');
    }

    public function xListUsersAction()
    {
        $this->request->defineParams(array(
            'sort' => array('type' => 'json')
        ));

        $sql = 'SELECT id, status, email, fullname, dtcreated, dtlastlogin, comments FROM account_users WHERE type = ? AND :FILTER:';
        $response = $this->buildResponseFromSql2($sql, array('id', 'status', 'email', 'fullname', 'dtcreated', 'dtlastlogin'), array('email', 'fullname'), array(Scalr_Account_User::TYPE_SCALR_ADMIN));
        foreach ($response["data"] as &$row) {
            $user = Scalr_Account_User::init();
            $user->loadById($row['id']);

            $row['dtcreated'] = Scalr_Util_DateTime::convertTz($row["dtcreated"]);
            $row['dtlastlogin'] = $row['dtlastlogin'] ? Scalr_Util_DateTime::convertTz($row["dtlastlogin"]) : 'Never';
        }
        $this->response->data($response);
    }

    public function createAction()
    {
        $this->response->page('ui/admin/users/create.js');
    }

    public function editAction()
    {
        $user = Scalr_Account_User::init();
        $user->loadById($this->getParam('userId'));

        if ($user->getEmail() == 'admin' && $user->getId() != $this->user->getId())
            throw new Scalr_Exception_InsufficientPermissions();

        $this->response->page('ui/admin/users/create.js', array(
            'user' => array(
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'fullname' => $user->fullname,
                'status' => $user->status,
                'comments' => $user->comments
            )
        ));
    }

    public function xSaveAction()
    {
        $user = Scalr_Account_User::init();

        if (! $this->getParam('email'))
            throw new Scalr_Exception_Core('Email cannot be null');

        if ($this->getParam('id')) {
            $user->loadById($this->getParam('id'));

            if ($user->getEmail() == 'admin' && $user->getId() != $this->user->getId())
                throw new Scalr_Exception_InsufficientPermissions();

            if ($user->getEmail() != 'admin')
                $user->updateEmail($this->getParam('email'));
        } else {
            $user->create($this->getParam('email'), $this->user->getAccountId());
            $user->type = Scalr_Account_User::TYPE_SCALR_ADMIN;
        }

        if ($this->getParam('password') != '******')
            $user->updatePassword($this->getParam('password'));

        if ($user->getEmail() != 'admin') {
            $user->status = $this->getParam('status');

            $user->fullname = $this->getParam('fullname');
            $user->comments = $this->getParam('comments');
        }

        $user->save();
        $this->response->success('User successfully saved');
    }

    public function xRemoveAction()
    {
        $user = Scalr_Account_User::init();
        $user->loadById($this->getParam('userId'));

        if ($user->getEmail() == 'admin')
            throw new Scalr_Exception_InsufficientPermissions();

        $user->delete();
        $this->response->success('User successfully removed');
    }
}
